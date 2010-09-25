/*
 *      usicprint.c
 *      
 *      Copyright 2008 Protas Oleksiy <elfy.ua@gmail.com>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */


#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <cups/cups.h>
#include <cups/ipp.h>

/* CUPS server DNS/IP */
/* NB: make this a configurable option! */
#define USIC_IPP_SERVER "172.16.200.6"

/* Connection to printer */
http_t *cupsServerLink=NULL;

void usage_n_exit()
{
	exit(1);
}

/* Populates routine IPP fields */
void setupRequest(ipp_t* r)
{
	ippAddString(r,IPP_TAG_OPERATION,IPP_TAG_NAME,"requesting-user-name",NULL,"usicprint");
	ippAddString(r,IPP_TAG_OPERATION,IPP_TAG_CHARSET,"attributes-charset",NULL,"utf-8");
	ippAddString(r,IPP_TAG_OPERATION,IPP_TAG_LANGUAGE,"attributes-natural-language",NULL,"en");
	ippAddString(r,IPP_TAG_OPERATION,IPP_TAG_URI,"printer-uri",NULL,"ipp://localhost/printers/HP_LaserJet_P2015"); // Nota Bene
}

/* Outputs the job queue
 * Currently: job URI, username, hostname */
int show_cups_queue(char loged_in_user[])
{
	/* Creating the request */
	ipp_t* r=ippNewRequest(IPP_GET_JOBS);
	setupRequest(r);
	ippAddString(r,IPP_TAG_OPERATION,IPP_TAG_KEYWORD,"which-jobs",NULL,"held");
	int job_count=0;
	/* Attributes selection */
	static const char *atar[] = {
			    "job-id",
			    "job-name",
			    "job-originating-user-name",
			    "job-originating-host-name",
			    "job-state"
			};
	ippAddStrings(r,IPP_TAG_OPERATION,IPP_TAG_KEYWORD,"requested-attributes",sizeof(atar)/sizeof(atar[0]),NULL,atar);	
	r=cupsDoRequest(cupsServerLink,r,"/admin/");
	if (cupsLastError()>IPP_OK_CONFLICT)
  	{
    	fprintf(stderr, "Error: %s\n", cupsLastErrorString());
    	return;
  	}
  	/* Parsing responce */
  	ipp_attribute_t *attr;
  	for (attr=r->attrs;attr!=NULL;attr=attr->next)
  	{
  		/* Skipping unneeded stuff */
		while ((attr!=NULL)&&(attr->group_tag!=IPP_TAG_JOB)) attr=attr->next;
		if (attr==NULL) break;
		int jobid=0;
		char *user=NULL;
		char *host=NULL;
		char *name="Untitled Job";
		int weWantThisJob=0; /* NB: Indian code! */
		int pages=0;
		
  		while ((attr!=NULL)&&(attr->group_tag==IPP_TAG_JOB)) 
		{
  		    if ((!strcmp(attr->name,"job-id"))&&(attr->value_tag==IPP_TAG_INTEGER))
			jobid=attr->values[0].integer;
  		    else if ((!strcmp(attr->name,"job-originating-user-name"))&&(attr->value_tag==IPP_TAG_NAME))
			user=attr->values[0].string.text;
  		    else if ((!strcmp(attr->name,"job-name"))&&(attr->value_tag==IPP_TAG_NAME))
    			name=attr->values[0].string.text;
 		    else if ((!strcmp(attr->name,"job-originating-host-name"))&&(attr->value_tag==IPP_TAG_NAME))
    			host=attr->values[0].string.text;
		    else if ((!strcmp(attr->name,"page-count"))&&(attr->value_tag==IPP_TAG_INTEGER))
			pages=attr->values[0].integer;
		    else if ((!strcmp(attr->name,"job-state"))) if (attr->values[0].integer==IPP_JOB_HELD) weWantThisJob=1; 
		    attr=attr->next;
		}
		if (weWantThisJob && (loged_in_user == NULL || !strcmp(loged_in_user,user)))
		{
		    printf("%i\t%s\t%s\t%s\t%i\n",jobid,user,host,name,pages);
		    job_count++;
		}
		if ((jobid==0)&&(attr!=NULL)) continue;
	        if (attr==NULL) break;
 	}
  	ippDelete(r);
	return job_count;
}

// CUPS does not support IPP_HOLD_NEW_JOBS *nuts*
// code commented until it finally will

/* Instructs CUPS to hold any new jobs so they can be managed by usicprint */
/* Warning: this sets the holder regardless whether it was set prior invokation or not */
/*void install_usicprint()
{
	ipp_t* r=ippNewRequest(IPP_HOLD_NEW_JOBS);
	setupRequest(r);
	cupsDoRequest(cupsServerLink,r,"/admin/");
	if (cupsLastError()>IPP_OK_CONFLICT)
  	{
    	fprintf(stderr, "%s\n", cupsLastErrorString());
  	}
	
}*/

/* Resumes normal CUPS operation */
/* Warning: same as above --- this just unsets the holder */
//void uninstall_usicprint()
//{
	/*ipp_t* r=ippNewRequest(IPP_RELEASE_HELD_JOBS);
	r=cupsDoRequest(http,r,printerName);*/
	 
//}

int allow_cups_task(int id)
{
	ipp_t* r=ippNewRequest(IPP_RELEASE_JOB);
	ippAddInteger(r,IPP_TAG_OPERATION,IPP_TAG_INTEGER,"job-id",id);
	setupRequest(r);
	cupsDoRequest(cupsServerLink,r,"/admin/");
	if (cupsLastError()>IPP_OK_CONFLICT)
  	{
    		fprintf(stderr, "Error: %s\n", cupsLastErrorString());
  		return 27; //task havn't been performed. it's needed for transactions to rollback
	}
	
	return 0;
}

void deny_cups_task(int id)
{
	ipp_t* r=ippNewRequest(IPP_CANCEL_JOB);
	ippAddInteger(r,IPP_TAG_OPERATION,IPP_TAG_INTEGER,"job-id",id);
	setupRequest(r);

	cupsDoRequest(cupsServerLink,r,"/admin/");
	if (cupsLastError()>IPP_OK_CONFLICT)
  	{
    	fprintf(stderr, "Error: %s\n", cupsLastErrorString());
  	}
}

int main(int argc, char** argv)
{
	/* Trying to connect to printer server */
	if ((cupsServerLink=httpConnect(USIC_IPP_SERVER,631))==NULL)
	{
		fprintf(stderr, "%s\n", cupsLastErrorString());
		fprintf(stderr,"Server refused our connection\n");
		return 15;
	}
	int returncode=0;
		
	if(!strcmp(argv[1],"show"))
        {
		if (argc == 2) returncode = (show_cups_queue(NULL)==0)?2:0;
		else returncode = (show_cups_queue(argv[2])==0)? 2:0 ; // If there are no tasks -- return 02, 00 otherwise
			//	else if (!strcmp(argv[1],"install")) install_usicprint();
			//	else if (!strcmp(argv[1],"uninstall")) uninstall_usicprint();
	}
	else
	{
	        /* Commands with task specification */
                int taskno=-1; sscanf(argv[2],"%i",&taskno);
                if (taskno<0)
                {
                	fprintf(stderr,"Task specification %i is invalid!\n",taskno);
                        return 1;
                }
                if (!strcmp(argv[1],"allow")) returncode = allow_cups_task(taskno);
                else if (!strcmp(argv[1],"deny")) deny_cups_task(taskno);
                else usage_n_exit();
        }
	
	/* Closing the connection */
	httpClose(cupsServerLink);
	return returncode;
}

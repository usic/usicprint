#include <stdio.h>
#include <stdlib.h>
#include <string.h>

int main(int argc, char** argv)
{
  //read file
  FILE * jobFile;
  char line[1024];
  char str[8];
  int num_pages = 0;

  char path_to_file[27] = "/var/spool/cups/d";
  jobFile = fopen (strcat(strcat(path_to_file,argv[1]),"-001"),"r");
  if (jobFile == NULL){fprintf(stderr, "Error open file");}
  else {
     while( fgets(line, sizeof(line), jobFile) != NULL ) {
        strncpy( str, line, 7 );
	str[7] = '\0';
        if(!strcmp(str,"%%Pages") && (line[9]<='9' && line[9]>='0')){
           int i=9;                                
	   while(line[i]!='\0' && line[i]<='9' && line[i]>='0'){
              num_pages = num_pages*10 + (line[i]-'0');
	      i++;
	   }
	   fclose(jobFile);
	}
   }
   printf("%d\n",num_pages); 
   fclose(jobFile);
  }
  return 0;
}

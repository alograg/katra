@ECHO OFF
MKDIR %3%1
dir /b %3*_fields.xml >.\temp\archivos.txt
FOR /F "delims=. tokens=1-2" %%f in (.\temp\archivos.txt) DO .\bin\msxsl %3%%f.%%g .\translates\fieldsTo%1.xsl -o %3%1\%%f.%2 -xw
ECHO OFF
ECHO TO INSTALL THE SERVICE, RUN THE FOLLOWING COMMAND:
ECHO installutil bin\Debug\ParentSteamService.exe
ECHO TO REMOVE THE SERVICE, RUN THE FOLLOWING COMMAND
ECHO installutil /u bin\Debug\ParentSteamService.exe
CALL %comspec% /k "C:\Program Files (x86)\Microsoft Visual Studio\2019\Community\Common7\Tools\VsDevCmd.bat"

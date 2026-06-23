Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "cmd /c cd /d C:\laragon\www\workwise && php spark rfid:listen-all", 0, False
Set WshShell = Nothing

<?
    // Klassendefinition
    class IPS2Enigma extends IPSModule 
    {
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
           	$this->RegisterMessage(0, IPS_KERNELSTARTED);
		
		$this->RegisterPropertyBoolean("Open", false);
	    	$this->RegisterPropertyString("User", "User");
	    	$this->RegisterPropertyString("Password", "Passwort");
		$this->RegisterPropertyString("IPAddress", "127.0.0.1");
		$this->RegisterPropertyInteger("DataUpdate", 15);
		$this->RegisterPropertyBoolean("HDD_Data", false);
		$this->RegisterPropertyBoolean("Movielist_Data", false);
		$this->RegisterPropertyBoolean("Movielist_Data_ShowShortDiscription", true);
		$this->RegisterPropertyBoolean("Movielist_Data_ShowSource", true);
		$this->RegisterPropertyBoolean("Movielist_Data_ShowMediaPlayer", true);
		$this->RegisterPropertyBoolean("Enigma2_Data", false);
		$this->RegisterPropertyBoolean("Signal_Data", false);
		$this->RegisterPropertyBoolean("Network_Data", false);
		$this->RegisterPropertyBoolean("RC_Data", false);
		$this->RegisterPropertyInteger("BouquetsNumber", 0);
		$this->RegisterPropertyBoolean("EPGnow_Data", false);
		$this->RegisterPropertyBoolean("EPGnext_Data", false);
		$this->RegisterPropertyInteger("EPGUpdate", 60);
		$this->RegisterPropertyBoolean("EPGlist_Data", false);
		$this->RegisterPropertyBoolean("EPGlist_Data_ShowShortDiscription", true);
		$this->RegisterPropertyBoolean("EPGlist_Data_ShowMediaPlayer", true);
		$this->RegisterPropertyBoolean("EPGlistSRef_Data", false);
		$this->RegisterPropertyBoolean("EPGlistSRef_Data_ShowShortDiscription", true);
		$this->RegisterPropertyInteger("PiconSource", 0);
		$this->RegisterPropertyBoolean("PiconUpdate", true);
		$this->RegisterPropertyInteger("ScreenshotUpdate", 30);
		$this->RegisterPropertyInteger("Screenshot", 640);
		$this->RegisterTimer("DataUpdate", 0, 'Enigma_Get_DataUpdate($_IPS["TARGET"]);');
		$this->RegisterTimer("EPGUpdate", 0, 'Enigma_Get_EPGUpdate($_IPS["TARGET"]);');
		$this->RegisterTimer("ScreenshotUpdate", 0, 'Enigma_GetScreenshot($_IPS["TARGET"]);');
		$this->RegisterTimer("StatusInfo", 0, 'Enigma_GetStatusInfo($_IPS["TARGET"]);');
		
		// Profile anlegen
		$this->RegisterProfileInteger("Enigma.Volume", "Melody", "", "", 0, 100, 1);
		$this->RegisterProfileInteger("Enigma.min", "Clock", "", " min", 0, 1000000, 1);
		$this->RegisterProfileInteger("Enigma.db", "Intensity", "", " db", 0, 1000000, 1);
		$this->RegisterProfileInteger("Enigma.GB", "Gauge", "", " GB", 0, 1000000, 1);
		
		$this->RegisterProfileInteger("Enigma.UpDown", "Shutter", "", "", 0, 1, 0);
		IPS_SetVariableProfileAssociation("Enigma.UpDown", 0, "+", "Shutter", -1);
		IPS_SetVariableProfileAssociation("Enigma.UpDown", 1, "-", "Shutter", -1);
		
		$this->RegisterProfileInteger("Enigma.YesNo", "Power", "", "", 0, 1, 0);
		IPS_SetVariableProfileAssociation("Enigma.YesNo", 0, "Nein", "Power", -1);
		IPS_SetVariableProfileAssociation("Enigma.YesNo", 1, "Ja", "Power", -1);
		
		// Status-Variablen anlegen
		$this->RegisterVariableString("e2devicename", "Model", "", 60);
		$this->RegisterVariableString("e2tunerinfo", "Tuner Information", "~HTMLBox", 65);
		
		$this->RegisterVariableBoolean("powerstate", "Powerstate", "~Switch", 100);
		$this->EnableAction("powerstate");
		$this->RegisterVariableInteger("isRecording", "Aufnahme", "Enigma.YesNo", 104);	
		$this->RegisterVariableInteger("Channel_UpDown", "Channel", "Enigma.UpDown", 105);
		$this->EnableAction("Channel_UpDown");	
		$this->RegisterVariableInteger("volume", "Volume", "Enigma.Volume", 106);
		$this->EnableAction("volume");
		$this->RegisterVariableInteger("volume_UpDown", "Volume", "Enigma.UpDown", 107);
		$this->EnableAction("volume_UpDown");	
		$this->RegisterVariableInteger("isMuted", "Mute", "Enigma.YesNo", 108);
		$this->EnableAction("isMuted");
		
		$this->RegisterVariableString("currservice_serviceref", "Service-Referenz", "", 108);
		$this->RegisterVariableString("e2servicename", "Service Name", "", 110);
		
		$this->RegisterVariableInteger("PiconUpdate", "Picon Update", "~UnixTimestamp", 1500);
		IPS_SetHidden($this->GetIDForIdent("PiconUpdate"), true);
	}
	    
	public function GetConfigurationForm() { 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 200, "icon" => "error", "caption" => "Instanz ist fehlerhaft"); 
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Kommunikationfehler!");
		
		$arrayElements = array(); 
		$arrayElements[] = array("type" => "CheckBox", "name" => "Open", "caption" => "Aktiv"); 
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "IPAddress", "caption" => "IP");
		$arrayElements[] = array("type" => "Label", "caption" => "Hinweis: Passwort für das WebIf muss deaktiviert sein!");
 		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Daten zum Enigma2-FTP Zugang (optional)");
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "User", "caption" => "User");
		$arrayElements[] = array("type" => "PasswordTextBox", "name" => "Password", "caption" => "Password");
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Zyklus Daten-Update in Sekunden (0 -> aus 1 -> Minimum)");
		$arrayElements[] = array("type" => "IntervalBox", "name" => "DataUpdate", "caption" => "Daten Update (sek)");
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "CheckBox", "name" => "Enigma2_Data", "caption" => "Enigma2 Daten anzeigen"); 
		$arrayElements[] = array("type" => "CheckBox", "name" => "HDD_Data", "caption" => "HDD Daten anzeigen");
		$arrayElements[] = array("type" => "CheckBox", "name" => "Network_Data", "caption" => "Netzwerk Daten anzeigen");
		$arrayElements[] = array("type" => "CheckBox", "name" => "Signal_Data", "caption" => "Empfangssignal Daten anzeigen");
		$arrayElements[] = array("type" => "CheckBox", "name" => "RC_Data", "caption" => "Virtuelle Fernbedienung erstellen");
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Zyklus EPG-Update in Sekunden (0 -> aus 15 -> Minimum) (HTML)");
		$arrayElements[] = array("type" => "IntervalBox", "name" => "EPGUpdate", "caption" => "EPG Update (sek)");
		$arrayElements[] = array("type" => "Label", "caption" => "Bei mehreren eingerichteten Bouquets muss hier eine Vorauswahl getroffen werden.");
		
		$BouquetsArray = array();
		$Bouquets = $this->GetBouquetsInformation();
		If ($Bouquets === false) {
			$arrayElements[] = array("type" => "Label", "caption" => "Es wurden keine Bouquets gefunden oder die Abfrage war fehlerhaft!");
		}
		else {
			$arrayOptions = array();
			foreach ($Bouquets as $Key => $Value) {
			   	$arrayOptions[] = array("label" => $Value, "value" => $Key);
			}
			$arrayElements[] = array("type" => "Select", "name" => "BouquetsNumber", "caption" => "Bouquets", "options" => $arrayOptions );	
		}
		$arrayElements[] = array("type" => "CheckBox", "name" => "EPGnow_Data", "caption" => "EPG des aktuellen Programms des aktuellen Senders anzeigen (einzelne Variablen/HTML)"); 
		$arrayElements[] = array("type" => "CheckBox", "name" => "EPGnext_Data", "caption" => "EPG des folgenden Programms des aktuellen Senders anzeigen (einzelne Variablen/HTML)");
				
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "CheckBox", "name" => "EPGlistSRef_Data", "caption" => "EPG des aktuellen und der folgenden Programms des aktuellen Senders anzeigen (HTML)");		
		$arrayElements[] = array("type" => "Label", "caption" => "Optionen zu Daten der Darstellung");	
		$arrayElements[] = array("type" => "CheckBox", "name" => "EPGlistSRef_Data_ShowShortDiscription", "caption" => "Kurzbeschreibung anzeigen");
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "CheckBox", "name" => "EPGlist_Data", "caption" => "EPG des aktuellen und der folgenden Programms aller Sender anzeigen (HTML)");
		$arrayElements[] = array("type" => "Label", "caption" => "Optionen zu Daten der Darstellung");		
		$arrayElements[] = array("type" => "CheckBox", "name" => "EPGlist_Data_ShowShortDiscription", "caption" => "Kurzbeschreibung anzeigen");
		$arrayElements[] = array("type" => "CheckBox", "name" => "EPGlist_Data_ShowMediaPlayer", "caption" => "Link zur Wiedergabe im Media Player anzeigen");
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "CheckBox", "name" => "Movielist_Data", "caption" => "Liste der Aufzeichnungen anzeigen (HTML)");
		$arrayElements[] = array("type" => "Label", "caption" => "Optionen zu Daten der Aufzeichnungen");
		$arrayElements[] = array("type" => "CheckBox", "name" => "Movielist_Data_ShowShortDiscription", "caption" => "Kurzbeschreibung anzeigen");
		$arrayElements[] = array("type" => "CheckBox", "name" => "Movielist_Data_ShowSource", "caption" => "Quelle anzeigen");
		$arrayElements[] = array("type" => "CheckBox", "name" => "Movielist_Data_ShowMediaPlayer", "caption" => "Link zur Wiedergabe im Media Player anzeigen");
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Quelle der für die HTML-Aufbereitung genutzten Picons:");
		$arrayElements[] = array("type" => "Label", "caption" => "(Voraussetzungen: FTP-Zugang zum Receiver und dort installierte Picons)");
		$arrayOptions = array();
		$arrayOptions[] = array("label" => "PHP-Modul", "value" => 0);
		$arrayOptions[] = array("label" => "Enigma-Receiver", "value" => 1);
		$arrayElements[] = array("type" => "Select", "name" => "PiconSource", "caption" => "Picon Quelle", "options" => $arrayOptions );
		$arrayElements[] = array("type" => "CheckBox", "name" => "PiconUpdate", "caption" => "Picon Update bei jedem Neustart des Moduls");
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Screenshot-Update in Sekunden (0 -> aus, 5 sek -> Minimum)");
		$arrayElements[] = array("type" => "IntervalBox", "name" => "ScreenshotUpdate", "caption" => "Screenshot-Update (sek)");
		$arrayOptions = array();
		$arrayOptions[] = array("label" => "640 px", "value" => 640);
		$arrayOptions[] = array("label" => "860 px", "value" => 860);
		$arrayOptions[] = array("label" => "1920 px", "value" => 1920);
		$arrayElements[] = array("type" => "Select", "name" => "Screenshot", "caption" => "Screenshot Grösse", "options" => $arrayOptions );		

		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");		
		
		$arrayElements[] = array("type" => "Label", "caption" => "Test Center"); 
		$arrayElements[] = array("type" => "TestCenter", "name" => "TestCenter");
			
		
		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	}        
	    
	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
		// Diese Zeile nicht löschen
		parent::ApplyChanges();
		$this->SetBuffer("FirstUpdate", "false");
		
		//Status-Variablen anlegen
		If ($this->ReadPropertyBoolean("Enigma2_Data") == true) {
			$this->RegisterVariableString("e2oeversion", "E2 OE-Version", "", 10);
			$this->RegisterVariableString("e2enigmaversion", "E2 Version", "", 20);
			$this->RegisterVariableString("e2distroversion", "E2 Distro-Version", "", 30);
			$this->RegisterVariableString("e2imageversion", "E2 Image-Version", "", 40);
			$this->RegisterVariableString("e2webifversion", "E2 WebIf-Version", "", 50);
		}
		$this->RegisterVariableString("e2devicename", "Model", "", 60);
		$this->RegisterVariableString("e2tunerinfo", "Tuner Information", "~HTMLBox", 65);
		
		If ($this->ReadPropertyBoolean("Network_Data") == true) {
			$this->RegisterVariableString("e2lanname", "Schnittstelle", "", 69);
			$this->RegisterVariableString("e2lanmac", "MAC", "", 70);
			$this->RegisterVariableBoolean("e2landhcp", "DHCP", "", 71);
			$this->RegisterVariableString("e2lanip", "IP", "", 72);
			$this->RegisterVariableString("e2lanmask", "Mask", "", 73);
			$this->RegisterVariableString("e2langw", "Gateway", "", 74);
		}
		If ($this->ReadPropertyBoolean("HDD_Data") == true) {
			$this->RegisterVariableString("e2hddinfo_model", "HDD Model", "", 80);
			$this->RegisterVariableInteger("e2hddinfo_capacity", "HDD Capacity", "Enigma.GB", 90);
			$this->RegisterVariableInteger("e2hddinfo_free", "HDD Free", "Enigma.GB", 95);
		}
		
		If ($this->ReadPropertyBoolean("EPGnow_Data") == true) {
			$this->RegisterVariableString("e2eventtitle", "Event Title", "", 120);
			$this->RegisterVariableString("e2eventdescription", "Event Description", "", 125);
			$this->RegisterVariableString("e2eventdescriptionextended", "Event Description Extended", "", 130);
			$this->RegisterVariableInteger("e2eventstart", "Event Start", "~UnixTimestampTime", 140);
			$this->RegisterVariableInteger("e2eventend", "Event End", "~UnixTimestampTime", 150);
			$this->RegisterVariableInteger("e2eventduration", "Event Duration", "Enigma.min", 160);		
			$this->RegisterVariableInteger("e2eventpast", "Event Past", "Enigma.min", 170);
			$this->RegisterVariableInteger("e2eventleft", "Event Left", "Enigma.min", 180);
			$this->RegisterVariableInteger("e2eventprogress", "Event Progress", "~Intensity.100", 190);
		}
		
		If ($this->ReadPropertyBoolean("EPGnext_Data") == true) {
			$this->RegisterVariableString("e2nexteventtitle", "Next Event Title", "", 200);
			$this->RegisterVariableString("e2nexteventdescription", "Next Event Description", "", 210);
			$this->RegisterVariableString("e2nexteventdescriptionextended", "Next Event Description Extended", "", 220);
			$this->RegisterVariableInteger("e2nexteventstart", "Next Event Start", "~UnixTimestampTime", 230);
			$this->RegisterVariableInteger("e2nexteventend", "Next Event End", "~UnixTimestampTime", 240);
			$this->RegisterVariableInteger("e2nexteventduration", "Next Event Duration", "Enigma.min", 250);	
		}
		
		If (($this->ReadPropertyBoolean("EPGnow_Data") == true) OR ($this->ReadPropertyBoolean("EPGnext_Data") == true)) {
			$this->RegisterVariableString("e2epgHTML", "EPG", "~HTMLBox", 257);
		}
		
		If ($this->ReadPropertyBoolean("Movielist_Data") == true) {
			$this->RegisterVariableString("e2movielist", "Aufzeichnungen", "~HTMLBox", 260);
		}
		
		If ($this->ReadPropertyBoolean("Signal_Data") == true) {
			$this->RegisterVariableInteger("e2snrdb", "Signal-to-Noise Ratio (dB)", "Enigma.db", 300);
			$this->RegisterVariableInteger("e2snr", "Signal-to-Noise Ratio", "~Intensity.100", 310);
			$this->RegisterVariableInteger("e2ber", "Bit error rate", "", 320);
			$this->RegisterVariableInteger("e2agc", "Automatic Gain Control", "~Intensity.100", 330);
		}
		
		If ($this->ReadPropertyBoolean("RC_Data") == true) {
			$this->RegisterVariableBoolean("rc_power", "Power", "~Switch", 500);
			$this->EnableAction("rc_power");
			$this->RegisterVariableBoolean("rc_mute", "Mute", "~Switch", 505);
			$this->EnableAction("rc_mute");
			$this->RegisterVariableBoolean("rc_vol_up", "Volume up", "~Switch", 510);
			$this->EnableAction("rc_vol_up");
			$this->RegisterVariableBoolean("rc_vol_down", "Volume down", "~Switch", 520);
			$this->EnableAction("rc_vol_down");
			$this->RegisterVariableBoolean("rc_1", "1", "~Switch", 530);
			$this->EnableAction("rc_1");
			$this->RegisterVariableBoolean("rc_2", "2", "~Switch", 540);
			$this->EnableAction("rc_2");
			$this->RegisterVariableBoolean("rc_3", "3", "~Switch", 550);
			$this->EnableAction("rc_3");
			$this->RegisterVariableBoolean("rc_4", "4", "~Switch", 560);
			$this->EnableAction("rc_4");
			$this->RegisterVariableBoolean("rc_5", "5", "~Switch", 570);
			$this->EnableAction("rc_5");
			$this->RegisterVariableBoolean("rc_6", "6", "~Switch", 580);
			$this->EnableAction("rc_6");
			$this->RegisterVariableBoolean("rc_7", "7", "~Switch", 590);
			$this->EnableAction("rc_7");
			$this->RegisterVariableBoolean("rc_8", "8", "~Switch", 600);
			$this->EnableAction("rc_8");
			$this->RegisterVariableBoolean("rc_9", "9", "~Switch", 610);
			$this->EnableAction("rc_9");
			$this->RegisterVariableBoolean("rc_0", "0", "~Switch", 620);
			$this->EnableAction("rc_0");
			$this->RegisterVariableBoolean("rc_previous", "Previous", "~Switch", 640);
			$this->EnableAction("rc_previous");
			$this->RegisterVariableBoolean("rc_next", "Next", "~Switch", 650);
			$this->EnableAction("rc_next");
			$this->RegisterVariableBoolean("rc_bouquet_up", "Bouquet up", "~Switch", 660);
			$this->EnableAction("rc_bouquet_up");
			$this->RegisterVariableBoolean("rc_bouquet_down", "Bouquet down", "~Switch", 670);
			$this->EnableAction("rc_bouquet_down");
			$this->RegisterVariableBoolean("rc_red", "Red", "~Switch", 680);
			$this->EnableAction("rc_red");
			$this->RegisterVariableBoolean("rc_green", "Green", "~Switch", 690);
			$this->EnableAction("rc_green");
			$this->RegisterVariableBoolean("rc_yellow", "Yellow", "~Switch", 700);
			$this->EnableAction("rc_yellow");
			$this->RegisterVariableBoolean("rc_blue", "Blue", "~Switch", 710);
			$this->EnableAction("rc_blue");
			$this->RegisterVariableBoolean("rc_up", "Up", "~Switch", 720);
			$this->EnableAction("rc_up");
			$this->RegisterVariableBoolean("rc_down", "Down", "~Switch", 730);
			$this->EnableAction("rc_down");
			$this->RegisterVariableBoolean("rc_left", "Left", "~Switch", 740);
			$this->EnableAction("rc_left");
			$this->RegisterVariableBoolean("rc_right", "Right", "~Switch", 750);
			$this->EnableAction("rc_right");
			$this->RegisterVariableBoolean("rc_audio", "Audio", "~Switch", 760);
			$this->EnableAction("rc_audio");
			$this->RegisterVariableBoolean("rc_video", "Video", "~Switch", 770);
			$this->EnableAction("rc_video");
			$this->RegisterVariableBoolean("rc_lame", "Lame", "~Switch", 780);
			$this->EnableAction("rc_lame");
			$this->RegisterVariableBoolean("rc_info", "Info", "~Switch", 790);
			$this->EnableAction("rc_info");
			$this->RegisterVariableBoolean("rc_menu", "Menu", "~Switch", 800);
			$this->EnableAction("rc_menu");
			$this->RegisterVariableBoolean("rc_ok", "OK", "~Switch", 810);
			$this->EnableAction("rc_ok");
			$this->RegisterVariableBoolean("rc_menu", "Menu", "~Switch", 800);
			$this->EnableAction("rc_menu");
			$this->RegisterVariableBoolean("rc_ok", "OK", "~Switch", 810);
			$this->EnableAction("rc_ok");
			$this->RegisterVariableBoolean("rc_tv", "TV", "~Switch", 820);
			$this->EnableAction("rc_tv");
			$this->RegisterVariableBoolean("rc_radio", "Radio", "~Switch", 830);
			$this->EnableAction("rc_radio");
			$this->RegisterVariableBoolean("rc_help", "Help", "~Switch", 840);
			$this->EnableAction("rc_help");
			$this->RegisterVariableBoolean("rc_text", "Text", "~Switch", 850);
			$this->EnableAction("rc_text");
			$this->RegisterVariableBoolean("rc_exit", "Exit", "~Switch", 860);
			$this->EnableAction("rc_exit");
			$this->RegisterVariableBoolean("rc_rewind", "Rewind", "~Switch", 870);
			$this->EnableAction("rc_rewind");
			$this->RegisterVariableBoolean("rc_play", "Play", "~Switch", 880);
			$this->EnableAction("rc_play");
			$this->RegisterVariableBoolean("rc_pause", "Pause", "~Switch", 890);
			$this->EnableAction("rc_pause");
			$this->RegisterVariableBoolean("rc_forward", "Forward", "~Switch", 900);
			$this->EnableAction("rc_forward");
			$this->RegisterVariableBoolean("rc_stop", "Stop", "~Switch", 910);
			$this->EnableAction("rc_stop");
			$this->RegisterVariableBoolean("rc_record", "Record", "~Switch", 920);
			$this->EnableAction("rc_record");
		}
		
		If ($this->ReadPropertyBoolean("EPGlist_Data") == true) {
			$this->RegisterVariableString("e2epglistHTML", "EPG Liste", "~HTMLBox", 950);
		}
		
		If ($this->ReadPropertyBoolean("EPGlistSRef_Data") == true) {
			$this->RegisterVariableString("e2epglistSRefHTML", "EPG Liste Sender", "~HTMLBox", 950);
		}
		
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			// Objekte und Hook anlegen
			if (IPS_GetKernelRunlevel() == KR_READY) {
				$this->RegisterMediaObject("Screenshot_".$this->InstanceID, "Screenshot_".$this->InstanceID, 1, $this->InstanceID, 1000, true, "Screenshot.jpg");
				$this->RegisterHook("/hook/IPS2Enigma");
			}
			
			If ($this->ReadPropertyInteger("PiconSource") == 0) {
				$this->Get_Picons();
			}
			elseif ($this->ReadPropertyInteger("PiconSource") == 1) {
				$this->Get_Picons_Enigma();
			}
			$this->Get_BasicData();
			$this->Get_HTML();
			$this->SetTimerInterval("DataUpdate", ($this->ReadPropertyInteger("DataUpdate") * 1000));
			$this->SetTimerInterval("EPGUpdate", ($this->ReadPropertyInteger("EPGUpdate") * 1000));
			$this->SetTimerInterval("ScreenshotUpdate", ($this->ReadPropertyInteger("ScreenshotUpdate") * 1000));
			$this->SetTimerInterval("StatusInfo", 2 * 1000);
			$this->Get_Powerstate();
			$this->GetScreenshot();
			$this->Get_EPGUpdate();
			$this->GetStatusInfo();
			If ($this->ReadPropertyBoolean("Movielist_Data") == true) {
				$this->GetMovieListUpdate();
			}
			$this->SetStatus(102);
		}
		else {
			$this->SetTimerInterval("DataUpdate", 0);
			$this->SetTimerInterval("EPGUpdate", 0);
			$this->SetTimerInterval("ScreenshotUpdate", 0);
			$this->SetTimerInterval("StatusInfo", 0);
			$this->SetStatus(104);
		}
        }
	
	public function RequestAction($Ident, $Value) 
	{
  		switch($Ident) {
			case "isMuted":
			    	If (($this->ReadPropertyBoolean("Open") == true) AND ($this->Get_Powerstate() == true)) {
					$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/vol?set=mute");
					If ($xmlResult === false) {
						$this->SendDebug("Get_DataUpdate", "Fehler beim Setzen der Lautstaerke!", 0);
						return;
					}	
				}
				break;
			case "volume":
			    	If (($this->ReadPropertyBoolean("Open") == true) AND ($this->Get_Powerstate() == true)) {
					$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/vol?set=set".$Value);
					If ($xmlResult === false) {
						$this->SendDebug("Get_DataUpdate", "Fehler beim Setzen der Lautstaerke!", 0);
						return;
					}	
				}
				break;
			case "volume_UpDown":
			    	If (($this->ReadPropertyBoolean("Open") == true) AND ($this->Get_Powerstate() == true)) {
					$this->SetValue($Ident, $Value);
					If ($Value == 0) {
						$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/vol?set=up");
						If ($xmlResult === false) {
							$this->SendDebug("Get_DataUpdate", "Fehler beim Setzen der Lautstaerke!", 0);
							return;
						}
					}
					else {
						$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/vol?set=down");
						If ($xmlResult === false) {
							$this->SendDebug("Get_DataUpdate", "Fehler beim Setzen der Lautstaerke!", 0);
							return;
						}
					}
				}
				break;
			case "Channel_UpDown":
			    	If (($this->ReadPropertyBoolean("Open") == true) AND ($this->Get_Powerstate() == true)) {
					$this->SetValue($Ident, $Value);
					If ($Value == 0) {
						// 106 Key "right"
						$this->SentRCCommand(106);
					}
					else {
						// 105 Key "left"
						$this->SentRCCommand(105);
					}
				}
				break;
			case "powerstate":
			    	If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
					// 116 Key "Power""
					$xmlResult = new SimpleXMLElement(file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/web/remotecontrol?command=116"));
					$this->Get_EPGUpdate();
				}
				break;
			case "rc_power":
			    	// 116 Key "Power""
				$this->SetValue($Ident, true);
				$this->SentRCCommand(116);
				$this->SetValue($Ident, false);				
				break;
			case "rc_vol_up":
			    	// 115 Key "volume up"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(115);
				$this->SetValue($Ident, false);
				break;
			case "rc_vol_down":
			    	// 114 Key "volume down"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(115);
				$this->SetValue($Ident, false);
				break;
			case "rc_mute":
				// 113 Key "mute"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(113);
				$this->SetValue($Ident, false);
				break;
			case "rc_1":
				// 2   Key "1"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(2);
				$this->SetValue($Ident, false);
				break;
			case "rc_2":
				// 3   Key "2"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(3);
				$this->SetValue($Ident, false);
				break;
			case "rc_3":
				// 4   Key "3"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(4);
				$this->SetValue($Ident, false);
				break;
			case "rc_4":
				// 5   Key "4"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(5);
				$this->SetValue($Ident, false);
				break;
			case "rc_5":
				// 6   Key "5"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(6);
				$this->SetValue($Ident, false);
				break;
			case "rc_6":
			    	// 7   Key "6"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(7);
				$this->SetValue($Ident, false);
				break;
			case "rc_7":
				// 8   Key "7"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(8);
				$this->SetValue($Ident, false);
				break;
			case "rc_8":
				// 9   Key "8"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(9);
				$this->SetValue($Ident, false);
				break;
			case "rc_9":
				// 10  Key "9"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(10);
				$this->SetValue($Ident, false);
				break;
			case "rc_0":
				// 11  Key "0"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(11);
				$this->SetValue($Ident, false);
				break;
			case "rc_previous":
				// 412 Key "previous"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(412);
				$this->SetValue($Ident, false);
				break;
			case "rc_next":
				// 407 Key "next"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(407);
				$this->SetValue($Ident, false);
				break;
			case "rc_bouquet_up":
				// 402 Key "bouquet up"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(402);
				$this->SetValue($Ident, false);
				break;
			case "rc_bouquet_down":
				// 403 Key "bouquet down"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(403);
				$this->SetValue($Ident, false);
				break;
			case "rc_red":
				// 398 Key "red"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(398);
				$this->SetValue($Ident, false);
				break;
			case "rc_green":
				// 399 Key "green"	
				$this->SetValue($Ident, true);
				$this->SentRCCommand(399);
				$this->SetValue($Ident, false);
				break;
			case "rc_yellow":
				// 400 Key "yellow"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(400);
				$this->SetValue($Ident, false);
				break;
			case "rc_blue":
				// 401 Key "blue"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(401);
				$this->SetValue($Ident, false);
				break;
			case "rc_up":
				// 103 Key "up"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(103);
				$this->SetValue($Ident, false);
				break;
			case "rc_down":
				// 108 Key "down"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(108);
				$this->SetValue($Ident, false);
				break;
			case "rc_left":
				// 105 Key "left"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(105);
				$this->SetValue($Ident, false);
				break;
			case "rc_right":
				// 106 Key "right"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(106);
				$this->SetValue($Ident, false);
				break;
			case "rc_audio":
				// 392 Key "audio"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(392);
				$this->SetValue($Ident, false);
				break;
			case "rc_video":
				// 393 Key "video"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(393);
				$this->SetValue($Ident, false);
				break;
			case "rc_lame":
				// 174 Key "lame"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(174);
				$this->SetValue($Ident, false);
				break;
			case "rc_info":
				// 358 Key "info"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(358);
				$this->SetValue($Ident, false);
				break;
			case "rc_menu":
				// 139 Key "menu"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(139);
				$this->SetValue($Ident, false);
				break;
			case "rc_ok":
				// 352 Key "OK"	
				$this->SetValue($Ident, true);
				$this->SentRCCommand(352);
				$this->SetValue($Ident, false);
				break;
			case "rc_tv":
				// 377 Key "tv"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(377);
				$this->SetValue($Ident, false);
				break;
			case "rc_radio":
				// 385 Key "radio"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(385);
				$this->SetValue($Ident, false);
				break;
			case "rc_text":
				// 388 Key "text"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(388);
				$this->SetValue($Ident, false);
				break;
			case "rc_help":
				// 138 Key "help"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(138);
				$this->SetValue($Ident, false);
				break;
			case "rc_exit":
				// 1 Key "exit"	
				$this->SetValue($Ident, true);
				$this->SentRCCommand(1);
				$this->SetValue($Ident, false);
				break;
			case "rc_rewind":
				// 168 Key "rewind"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(168);
				$this->SetValue($Ident, false);
				break;
			case "rc_play":
				// 207 Key "play"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(207);
				$this->SetValue($Ident, false);
				break;
			case "rc_pause":
				// 119 Key "pause"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(119);
				$this->SetValue($Ident, false);
				break;
			case "rc_forward":
				// 208 Key "forward"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(208);
				$this->SetValue($Ident, false);
				break;
			case "rc_stop":
				// 128 Key "stop" 
				$this->SetValue($Ident, true);
				$this->SentRCCommand(128);
				$this->SetValue($Ident, false);
				break;
			case "rc_record":
				// 167 Key "record"
				$this->SetValue($Ident, true);
				$this->SentRCCommand(167);
				$this->SetValue($Ident, false);
				break;
			default:
			    throw new Exception("Invalid Ident");
	    	}
	}
	
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    	{
		switch ($Message) {
			case 10001:
				$this->RegisterMediaObject("Screenshot_".$this->InstanceID, "Screenshot_".$this->InstanceID, 1, $this->InstanceID, 1000, true, "Screenshot.jpg");
				$this->RegisterHook("/hook/IPS2Enigma");
				break;
			
		}
    	}            
	    
	// Beginn der Funktionen
	private function GetContent(string $HTTP_Link) {
    		$Content = @file_get_contents($HTTP_Link);
    		If ($Content === false) {
			$this->SendDebug("GetContent", "Fehler bei der Datenermittlung", 0);
        		$this->SetStatus(202);
			return false;
    		}
    		else {
        		If ($this->isXML($Content) == true) {
				$xmlResult = new SimpleXMLElement($Content);
        			$this->SetStatus(102);
				return $xmlResult;
			}
			else {
				$this->SetStatus(202);
			}
    		}
	}
	
	private function isXML($xml)
	{
    		libxml_use_internal_errors(true);

    		$doc = new DOMDocument('1.0', 'utf-8');
	    	$doc->loadXML($xml);

	    	$errors = libxml_get_errors();

	    	if(empty($errors)){
			return true;
	    	}

	    	$error = $errors[0];
	    	if($error->level < 3){
			$this->SendDebug("isXML", "XML mit Fehlern < Level 3!", 0);
			return true;
	    	}

	    	$explodedxml = explode("r", $xml);
	    	$badxml = $explodedxml[($error->line)-1];

	    	$message = $error->message . ' at line ' . $error->line . '. Bad XML: ' . htmlentities($badxml);
		$this->SendDebug("isXML", "XML mit Fehlern: ".$message, 0);
	return false;
	}    
	
	public function SentRCCommand(int $Key)
	{
		// $Key nummerisch einschränken
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->Get_Powerstate() == true)) {
			$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/remotecontrol?command=".$Key);
			// $Command hat einen echten Rückgabewert
			If ($xmlResult === false) {
				$this->SendDebug("SentRCCommand", "Fehler bei der Ausfuehrung!", 0);
				return;
			}
			else {
				If ((String)$xmlResult->e2result == "True") {
					$this->SendDebug("SentRCCommand", "Befehl erfolgreich gesendet", 0);
				}
				else {
					$this->SendDebug("SentRCCommand", (String)$xmlResult->e2resulttext, 0);
				}
			}
		}
	}
	    
	public function Get_DataUpdate()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND (GetValueBoolean($this->GetIDForIdent("powerstate")) == true)) {
			$this->SendDebug("Get_DataUpdate", "Ausfuehrung", 0);
			$this->SetBuffer("FirstUpdate", "false");
			// das aktuelle Programm
			$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/subservices");
       			If ($xmlResult === false) {
				$this->SendDebug("Get_DataUpdate", "Fehler beim Lesen der aktuellen Programm-Daten!", 0);
				return;
			}
			
			//SetValueString($this->GetIDForIdent("e2servicename"), (string)$xmlResult->e2service->e2servicename);
			$e2servicereference = (string)$xmlResult->e2service->e2servicereference;
			$e2servicename = (string)$xmlResult->e2service->e2servicename;	
						
			If ($this->ReadPropertyBoolean("Movielist_Data") == true) {
				$this->GetMovieListUpdate();
			}
			
			If ($this->ReadPropertyBoolean("HDD_Data") == true) {
				// Festplattendaten
				$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/about");
				If ($xmlResult === false) {
					$this->SendDebug("Get_DataUpdate", "Fehler beim Lesen der HDD-Daten!", 0);
					return;
				}
				
				If (substr($xmlResult->e2about->e2hddinfo->capacity, -2) == "GB") {
					SetValueInteger($this->GetIDForIdent("e2hddinfo_capacity"), (int)$xmlResult->e2about->e2hddinfo->capacity);
				}
				else {
					SetValueInteger($this->GetIDForIdent("e2hddinfo_capacity"), (int)$xmlResult->e2about->e2hddinfo->capacity * 1000);
				}
				If (substr($xmlResult->e2about->e2hddinfo->free, -2) == "GB") {	
					SetValueInteger($this->GetIDForIdent("e2hddinfo_free"), (int)$xmlResult->e2about->e2hddinfo->free);
				}
				else {
					SetValueInteger($this->GetIDForIdent("e2hddinfo_free"), (int)$xmlResult->e2about->e2hddinfo->free * 1000);
				}
			}
			
			//SetValueString($this->GetIDForIdent("e2stream"), "<video width="320" height="240" controls> <source src="http://".$this->ReadPropertyString("IPAddress")."/web/stream.m3u?ref=".$e2servicereference." type="video/mp4"> </video>");
			//"http://".$this->ReadPropertyString("IPAddress")."/web/stream.m3u?ref=".$e2servicereference
		}
		else {
			if ($this->GetBuffer("FirstUpdate") == "false") {
				If ($this->ReadPropertyBoolean("EPGnow_Data") == true) {
					SetValueString($this->GetIDForIdent("e2eventtitle"), "N/A");
					SetValueString($this->GetIDForIdent("e2eventdescription"), "N/A");
					SetValueString($this->GetIDForIdent("e2eventdescriptionextended"), "N/A");
					SetValueInteger($this->GetIDForIdent("e2eventstart"), 0);
					SetValueInteger($this->GetIDForIdent("e2eventend"), 0);
					SetValueInteger($this->GetIDForIdent("e2eventduration"), 0);
					SetValueInteger($this->GetIDForIdent("e2eventpast"), 0);
					SetValueInteger($this->GetIDForIdent("e2eventleft"), 0);
					SetValueInteger($this->GetIDForIdent("e2eventprogress"), 0);
					SetValueString($this->GetIDForIdent("e2epgHTML"), ""); 		
				}
				If ($this->ReadPropertyBoolean("EPGnext_Data") == true) {
					SetValueString($this->GetIDForIdent("e2nexteventtitle"), "N/A");
					SetValueString($this->GetIDForIdent("e2nexteventdescription"), "N/A");
					SetValueString($this->GetIDForIdent("e2nexteventdescriptionextended"), "N/A");
					SetValueInteger($this->GetIDForIdent("e2nexteventstart"), 0);
					SetValueInteger($this->GetIDForIdent("e2nexteventend"), 0);
					SetValueInteger($this->GetIDForIdent("e2nexteventduration"), 0);
					SetValueString($this->GetIDForIdent("e2epgHTML"), "");
				}
				$this->SetBuffer("FirstUpdate", "true");
			}
		}
	}
	
	public function Get_EPGUpdate()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			$this->SendDebug("Get_EPGUpdate", "Ausfuehrung", 0);
			$FilePathStream = "user".DIRECTORY_SEPARATOR."Enigma_HTML".DIRECTORY_SEPARATOR."Button-Media-Player_32.png";
			$FilePathPlay = "user".DIRECTORY_SEPARATOR."Enigma_HTML".DIRECTORY_SEPARATOR."Button-Play_32.png";
			
			$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/getservices");
			If ($xmlResult === false) {
				$this->SendDebug("Get_EPGUpdate", "Fehler beim Lesen der EPG-Daten!", 0);
				return;
			}
			$bouquet = (string)$xmlResult->e2service[$this->ReadPropertyInteger("BouquetsNumber")]->e2servicereference;
			
			// Gesamtliste aller Sender 
			$this->GetEPGNowNextData();

			// Programm des aktuellen Senders
			$this->GetEPGNowNextDataSRef();
			
			If (GetValueBoolean($this->GetIDForIdent("powerstate")) == true) {
				$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/subservices");
				If ($xmlResult === false) {
					$this->SendDebug("Get_EPGUpdate", "Fehler beim Lesen der EPG-Daten!", 0);
					return;
				}
				
				$e2servicereference = (string)$xmlResult->e2service->e2servicereference;
				$e2servicename = (string)$xmlResult->e2service->e2servicename;
				
				
				If (($this->ReadPropertyBoolean("EPGnow_Data") == true) AND ($this->ReadPropertyBoolean("EPGnext_Data") == false) AND (substr($e2servicereference, 0, 20) <> "1:0:0:0:0:0:0:0:0:0:")) {
					// das aktuelle Ereignis
					$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/epgservicenow?sRef=".$e2servicereference);
					If ($xmlResult === false) {
						$this->SendDebug("Get_EPGUpdate", "Fehler beim Lesen der EPG-Daten!", 0);
						return;
					}
					
					SetValueString($this->GetIDForIdent("e2eventtitle"), (string)utf8_decode($xmlResult->e2event->e2eventtitle));
					SetValueString($this->GetIDForIdent("e2eventdescription"), (string)utf8_decode($xmlResult->e2event->e2eventdescription));
					SetValueString($this->GetIDForIdent("e2eventdescriptionextended"), (string)utf8_decode($xmlResult->e2event->e2eventdescriptionextended));
					SetValueInteger($this->GetIDForIdent("e2eventstart"), (int)$xmlResult->e2event->e2eventstart);
					SetValueInteger($this->GetIDForIdent("e2eventend"), (int)$xmlResult->e2event->e2eventstart + (int)$xmlResult->e2event->e2eventduration);
					SetValueInteger($this->GetIDForIdent("e2eventduration"), round((int)$xmlResult->e2event->e2eventduration / 60) );
					SetValueInteger($this->GetIDForIdent("e2eventpast"), round( (int)time() - (int)$xmlResult->e2event->e2eventstart) / 60 );
					SetValueInteger($this->GetIDForIdent("e2eventleft"), round(((int)$xmlResult->e2event->e2eventstart + (int)$xmlResult->e2event->e2eventduration - (int)time()) / 60 ));
					SetValueInteger($this->GetIDForIdent("e2eventprogress"), GetValueInteger($this->GetIDForIdent("e2eventpast")) / GetValueInteger($this->GetIDForIdent("e2eventduration")) * 100);
					$table = '<style type="text/css">';
					$table .= '<link rel="stylesheet" href="./.../webfront.css">';
					$table .= "</style>";
					$table .= '<table class="tg">';
					$table .= "<tr>";
					$table .= '<th class="tg-kv4b" align="left" width=150 >Sender</th>';
					$table .= '<th class="tg-kv4b">Titel</th>';
					$table .= '<th class="tg-kv4b">Kurzbeschreibung<br></th>';
					$table .= '<th class="tg-kv4b">Langbeschreibung<br></th>';
					$table .= '<th class="tg-kv4b">Beginn<br></th>';
					$table .= '<th class="tg-kv4b">Ende<br></th>';
					$table .= '<th class="tg-kv4b">Dauer<br></th>';
					$table .= '</tr>';
					$table .= '<tr>';
					$table .= '<td class="tg-611x"><img src='.$this->Get_Filename($e2servicereference).' alt='.$e2servicename.'></td>';
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event->e2eventtitle).'</td>';
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event->e2eventdescription).'</td>';
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event->e2eventdescriptionextended).'</td>';
					$table .= '<td class="tg-611x">'.date("H:i", (int)$xmlResult->e2event->e2eventstart).' Uhr'.'</td>';
					$table .= '<td class="tg-611x">'.date("H:i", (int)$xmlResult->e2event->e2eventstart + (int)$xmlResult->e2event->e2eventduration).' Uhr'.'</td>';
					$table .= '<td class="tg-611x">'.round((int)$xmlResult->e2event->e2eventduration / 60).' min'.'</td>';
					$table .= '</tr>';
					$table .= '</table>';
					SetValueString($this->GetIDForIdent("e2epgHTML"), $table);
				}
				If (($this->ReadPropertyBoolean("EPGnow_Data") == false) AND ($this->ReadPropertyBoolean("EPGnext_Data") == true) AND (substr($e2servicereference, 0, 20) <> "1:0:0:0:0:0:0:0:0:0:")) {
					// das folgende Ereignis
					$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/epgservicenext?sRef=".$e2servicereference);
					If ($xmlResult === false) {
						$this->SendDebug("Get_EPGUpdate", "Fehler beim Lesen der EPG-Daten!", 0);
						return;
					}
					SetValueString($this->GetIDForIdent("e2nexteventtitle"), (string)utf8_decode($xmlResult->e2event->e2eventtitle));
					SetValueString($this->GetIDForIdent("e2nexteventdescription"), (string)utf8_decode($xmlResult->e2event->e2eventdescription));
					SetValueString($this->GetIDForIdent("e2nexteventdescriptionextended"), (string)utf8_decode($xmlResult->e2event->e2eventdescriptionextended));
					SetValueInteger($this->GetIDForIdent("e2nexteventstart"), (int)$xmlResult->e2event->e2eventstart);
					SetValueInteger($this->GetIDForIdent("e2nexteventend"), (int)$xmlResult->e2event->e2eventstart + (int)$xmlResult->e2event->e2eventduration);
					SetValueInteger($this->GetIDForIdent("e2nexteventduration"), round((int)$xmlResult->e2event->e2eventduration / 60) );
					$table = '<style type="text/css">';
					$table .= '<link rel="stylesheet" href="./.../webfront.css">';
					$table .= "</style>";
					$table .= '<table class="tg">';
					$table .= "<tr>";
					$table .= '<th class="tg-kv4b" align="left" width=150 >Sender</th>';
					$table .= '<th class="tg-kv4b">Titel</th>';
					$table .= '<th class="tg-kv4b">Kurzbeschreibung<br></th>';
					$table .= '<th class="tg-kv4b">Langbeschreibung<br></th>';
					$table .= '<th class="tg-kv4b">Beginn<br></th>';
					$table .= '<th class="tg-kv4b">Ende<br></th>';
					$table .= '<th class="tg-kv4b">Dauer<br></th>';
					$table .= '</tr>';
					$table .= '<tr>';
					$table .= '<td class="tg-611x"><img src='.$this->Get_Filename($e2servicereference).' alt='.$e2servicename.'></td>';
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event->e2eventtitle).'</td>';
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event->e2eventdescription).'</td>';
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event->e2eventdescriptionextended).'</td>';
					$table .= '<td class="tg-611x">'.date("H:i", (int)$xmlResult->e2event->e2eventstart).' Uhr'.'</td>';
					$table .= '<td class="tg-611x">'.date("H:i", (int)$xmlResult->e2event->e2eventstart + (int)$xmlResult->e2event->e2eventduration).' Uhr'.'</td>';
					$table .= '<td class="tg-611x">'.round((int)$xmlResult->e2event->e2eventduration / 60).' min'.'</td>';
					$table .= '</tr>';
					$table .= '</table>';
					SetValueString($this->GetIDForIdent("e2epgHTML"), $table);
				}
				If (($this->ReadPropertyBoolean("EPGnow_Data") == true) AND ($this->ReadPropertyBoolean("EPGnext_Data") == true) AND (substr($e2servicereference, 0, 20) <> "1:0:0:0:0:0:0:0:0:0:")) {
					// das aktuelle Ereignis
					$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/epgservicenow?sRef=".$e2servicereference);
					If ($xmlResult === false) {
						$this->SendDebug("Get_EPGUpdate", "Fehler beim Lesen der EPG-Daten!", 0);
						return;
					}
					SetValueString($this->GetIDForIdent("e2eventtitle"), (string)utf8_decode($xmlResult->e2event->e2eventtitle));
					SetValueString($this->GetIDForIdent("e2eventdescription"), (string)utf8_decode($xmlResult->e2event->e2eventdescription));
					SetValueString($this->GetIDForIdent("e2eventdescriptionextended"), (string)utf8_decode($xmlResult->e2event->e2eventdescriptionextended));
					SetValueInteger($this->GetIDForIdent("e2eventstart"), (int)$xmlResult->e2event->e2eventstart);
					SetValueInteger($this->GetIDForIdent("e2eventend"), (int)$xmlResult->e2event->e2eventstart + (int)$xmlResult->e2event->e2eventduration);
					SetValueInteger($this->GetIDForIdent("e2eventduration"), round((int)$xmlResult->e2event->e2eventduration / 60) );
					SetValueInteger($this->GetIDForIdent("e2eventpast"), round( (int)time() - (int)$xmlResult->e2event->e2eventstart) / 60 );
					SetValueInteger($this->GetIDForIdent("e2eventleft"), round(((int)$xmlResult->e2event->e2eventstart + (int)$xmlResult->e2event->e2eventduration - (int)time()) / 60 ));
					SetValueInteger($this->GetIDForIdent("e2eventprogress"), GetValueInteger($this->GetIDForIdent("e2eventpast")) / GetValueInteger($this->GetIDForIdent("e2eventduration")) * 100);
					// das folgende Ereignis
					$xmlResult_2 = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/epgservicenext?sRef=".$e2servicereference);
					If ($xmlResult_2 === false) {
						$this->SendDebug("Get_EPGUpdate", "Fehler beim Lesen der EPG-Daten!", 0);
						return;
					}
					SetValueString($this->GetIDForIdent("e2nexteventtitle"), (string)utf8_decode($xmlResult_2->e2event->e2eventtitle));
					SetValueString($this->GetIDForIdent("e2nexteventdescription"), (string)utf8_decode($xmlResult_2->e2event->e2eventdescription));
					SetValueString($this->GetIDForIdent("e2nexteventdescriptionextended"), (string)utf8_decode($xmlResult_2->e2event->e2eventdescriptionextended));
					SetValueInteger($this->GetIDForIdent("e2nexteventstart"), (int)$xmlResult_2->e2event->e2eventstart);
					SetValueInteger($this->GetIDForIdent("e2nexteventend"), (int)$xmlResult_2->e2event->e2eventstart + (int)$xmlResult_2->e2event->e2eventduration);
					SetValueInteger($this->GetIDForIdent("e2nexteventduration"), round((int)$xmlResult_2->e2event->e2eventduration / 60) );
					$table = '<style type="text/css">';
					$table .= '<link rel="stylesheet" href="./.../webfront.css">';
					$table .= "</style>";
					$table .= '<table class="tg">';
					$table .= "<tr>";
					$table .= '<th class="tg-kv4b" align="left" width=150 >Sender</th>';
					$table .= '<th class="tg-kv4b">Beginn<br></th>';
					$table .= '<th class="tg-kv4b">Titel</th>';
					$table .= '<th class="tg-kv4b">Kurzbeschreibung<br></th>';
					$table .= '<th class="tg-kv4b">Langbeschreibung<br></th>';
					//$table .= '<th class="tg-kv4b">Ende<br></th>';
					$table .= '<th class="tg-kv4b">Dauer<br></th>';
					$table .= '<colgroup>'; 
					$table .= '<col width="120">'; 
					$table .= '<col width="100">'; 
					$table .= '</colgroup>';
					$table .= '</tr>';
					$table .= '<tr>';
					$table .= '<td class="tg-611x"><img src='.$this->Get_Filename($e2servicereference).' alt='.$e2servicename.'></td>';
					$table .= '<td class="tg-611x">'.date("H:i", (int)$xmlResult->e2event->e2eventstart).' Uhr'.'</td>';
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event->e2eventtitle).'</td>';
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event->e2eventdescription).'</td>';
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event->e2eventdescriptionextended).'</td>';
					//$table .= '<td class="tg-611x">'.date("H:i", (int)$xmlResult->e2event->e2eventstart + (int)$xmlResult->e2event->e2eventduration).' Uhr'.'</td>';
					$table .= '<td class="tg-611x">'.round((int)$xmlResult->e2event->e2eventduration / 60).' min'.'</td>';
					$table .= '</tr>';
					$table .= '<tr>';
					$table .= '<td class="tg-611x"></td>';
					$table .= '<td class="tg-611x">'.date("H:i", (int)$xmlResult_2->e2event->e2eventstart).' Uhr'.'</td>';
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult_2->e2event->e2eventtitle).'</td>';
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult_2->e2event->e2eventdescription).'</td>';
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult_2->e2event->e2eventdescriptionextended).'</td>';
					//$table .= '<td class="tg-611x">'.date("H:i", (int)$xmlResult_2->e2event->e2eventstart + (int)$xmlResult_2->e2event->e2eventduration).' Uhr'.'</td>';
					$table .= '<td class="tg-611x">'.round((int)$xmlResult_2->e2event->e2eventduration / 60).' min'.'</td>';
					$table .= '</tr>';
					$table .= '</table>';
					SetValueString($this->GetIDForIdent("e2epgHTML"), $table);
				}
			}
			else {
				If (($this->ReadPropertyBoolean("EPGnow_Data") == true) OR ($this->ReadPropertyBoolean("EPGnext_Data") == true)) {
					SetValueString($this->GetIDForIdent("e2epgHTML"), "N/A");
				}
				If (($this->ReadPropertyBoolean("EPGlistSRef_Data") == true) ) {
					SetValueString($this->GetIDForIdent("e2epglistSRefHTML"), "N/A");
				}
			}
		}
	}
	
	// Ermittlung der Basisdaten
	private function Get_BasicData()
	{
		$this->SendDebug("Get_BasicData", "Ausfuehrung", 0);
		$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/deviceinfo");
		If ($xmlResult === false) {
			$this->SendDebug("Get_BasicData", "Fehler beim Lesen der Basis-Daten!", 0);
			return;
		}
		If ($this->ReadPropertyBoolean("Enigma2_Data") == true) {
			SetValueString($this->GetIDForIdent("e2oeversion"), (string)$xmlResult->e2oeversion);
			SetValueString($this->GetIDForIdent("e2enigmaversion"), (string)$xmlResult->e2enigmaversion);
			SetValueString($this->GetIDForIdent("e2distroversion"), (string)$xmlResult->e2distroversion);
			SetValueString($this->GetIDForIdent("e2imageversion"), (string)$xmlResult->e2imageversion);
			SetValueString($this->GetIDForIdent("e2webifversion"), (string)$xmlResult->e2webifversion);
		}
		SetValueString($this->GetIDForIdent("e2devicename"), (string)$xmlResult->e2devicename);
		$table = '<style type="text/css">';
		$table .= '<link rel="stylesheet" href="./.../webfront.css">';
		$table .= "</style>";
		$table .= '<table class="tg">';
		$table .= "<tr>";
		$table .= '<th class="tg-kv4b">Name</th>';
		$table .= '<th class="tg-kv4b">Typ<br></th>';
		$table .= '</tr>';
		for ($i = 0; $i <= count($xmlResult->e2frontends->e2frontend) - 1; $i++) {
			$table .= '<tr>';
			$table .= '<td class="tg-611x">'.$xmlResult->e2frontends->e2frontend[$i]->e2name.'</td>';
			$table .= '<td class="tg-611x">'.$xmlResult->e2frontends->e2frontend[$i]->e2model.'</td>';
			$table .= '</tr>';
		}
		$table .= '</table>';
		SetValueString($this->GetIDForIdent("e2tunerinfo"), $table);
		
		If ($this->ReadPropertyBoolean("Network_Data") == true) {
			$this->SetValue("e2lanname", (string)$xmlResult->e2network->e2interface->e2name);
			$this->SetValue("e2lanmac", (string)$xmlResult->e2network->e2interface->e2mac);
			If ((string)$xmlResult->e2network->e2interface->e2dhcp == "False") {
				$this->SetValue("e2landhcp", false);
			}
			else {
				$this->SetValue("e2landhcp", true);
			}
			$this->SetValue("e2lanip", (string)$xmlResult->e2network->e2interface->e2ip);
			$this->SetValue("e2lanmask", (string)$xmlResult->e2network->e2interface->e2netmask);
			$this->SetValue("e2langw", (string)$xmlResult->e2network->e2interface->e2gateway);
		}
		
		If ($this->ReadPropertyBoolean("HDD_Data") == true) {
			SetValueString($this->GetIDForIdent("e2hddinfo_model"), (string)$xmlResult->e2hdds->e2hdd->e2model);
		}
	}
	
	public function GetStatusInfo()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			$this->SendDebug("GetStatusInfo", "Ausfuehrung", 0);
			$JSONString = file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/api/statusinfo?");
			If ($JSONString == false) {
				$this->SendDebug("GetStatusInfo", "Fehler beim Lesen der Statusinformationen!", 0);
				return;
			}
			$data = json_decode($JSONString);
			// Prüfen ob die Box ein- oder ausgeschaltet ist
			If (!filter_var($data->inStandby, FILTER_VALIDATE_BOOLEAN) <> GetValueBoolean($this->GetIDForIdent("powerstate")) ) {
				SetValueBoolean($this->GetIDForIdent("powerstate"), !filter_var($data->inStandby, FILTER_VALIDATE_BOOLEAN));
			}
			
			$this->SendDebug("GetStatusInfo", "Recording: ".$data->isRecording, 0);
			// Prüfen ob eine Aufnahme läuft
			If (filter_var($data->isRecording, FILTER_VALIDATE_BOOLEAN) <> $this->GetValue("isRecording") ) {
				$this->SetValue("isRecording", intval(filter_var($data->isRecording, FILTER_VALIDATE_BOOLEAN)) );
				
				If ($this->ReadPropertyBoolean("Movielist_Data") == true) {
					$this->GetMovieListUpdate();
				}
			}
			
			// Prüfen ob gemuted ist	
			If ($this->GetValue("isMuted") <> intval($data->muted)) {
				$this->SetValue("isMuted", intval($data->muted));
			}	
			
			
			// Lautstärke
			If (intval($data->volume) <> $this->GetValue("volume") ) {
				$this->SetValue("volume", intval($data->volume));
			}
			
			If (!filter_var($data->inStandby, FILTER_VALIDATE_BOOLEAN) == true) {
				// Der aktuelle Programm-Name
				If (isset($data->currservice_station) ) {
					If (strval($data->currservice_station) <> GetValueString($this->GetIDForIdent("e2servicename")) ) {
						SetValueString($this->GetIDForIdent("e2servicename"), strval($data->currservice_station));

					}
				}
				else {
					If (GetValueString($this->GetIDForIdent("e2servicename")) <> "N/A") {
						SetValueString($this->GetIDForIdent("e2servicename"), "N/A");
					}
				}
				// Der aktuelle Service-Referenz
				If (isset($data->currservice_serviceref) ) {
					If (strval($data->currservice_serviceref) <> GetValueString($this->GetIDForIdent("currservice_serviceref")) ) {
						SetValueString($this->GetIDForIdent("currservice_serviceref"), strval($data->currservice_serviceref));
						// Programm des aktuellen Senders
						$this->GetEPGNowNextDataSRef();
						// Liste aller Programme
						$this->GetEPGNowNextData();
					}
				}
				else {
					If (GetValueString($this->GetIDForIdent("currservice_serviceref")) <> "N/A") {
						SetValueString($this->GetIDForIdent("currservice_serviceref"), "N/A");
					}
				}
				// Signalstärke
				If ($this->ReadPropertyBoolean("Signal_Data") == true) {
					// Empfangsstärke ermitteln
					$xmlResult = new SimpleXMLElement(file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/web/signal?"));
					SetValueInteger($this->GetIDForIdent("e2snrdb"), (int)$xmlResult->e2snrdb);
					SetValueInteger($this->GetIDForIdent("e2snr"), (int)$xmlResult->e2snr);
					SetValueInteger($this->GetIDForIdent("e2ber"), (int)$xmlResult->e2ber);
					SetValueInteger($this->GetIDForIdent("e2agc"), (int)$xmlResult->e2acg);
				}
			}
			else {
				// Signalstärke
				If ($this->ReadPropertyBoolean("Signal_Data") == true) {
					If (GetValueInteger($this->GetIDForIdent("e2snrdb")) <> 0 ) {
						SetValueInteger($this->GetIDForIdent("e2snrdb"), 0);
						SetValueInteger($this->GetIDForIdent("e2snr"), 0);
						SetValueInteger($this->GetIDForIdent("e2ber"), 0);
						SetValueInteger($this->GetIDForIdent("e2agc"), 0);
					}
				}
				/*
				If (GetValueString($this->GetIDForIdent("e2servicename")) <> "N/A" ) {
					SetValueString($this->GetIDForIdent("e2servicename"), "N/A");
				}
				
				If (GetValueString($this->GetIDForIdent("currservice_serviceref")) <> "N/A" ) {
					SetValueString($this->GetIDForIdent("currservice_serviceref"), "N/A");
				}
				*/
			}
			
		}
	}
	
	private function GetMovieListUpdate()
	{
		// Erstellt eine HTML-Tabelle mit allen Aufzeichnungen
		$this->SendDebug("GetMovieListUpdate", "Ausfuehrung", 0);
		$FilePathPlay = "user".DIRECTORY_SEPARATOR."Enigma_HTML".DIRECTORY_SEPARATOR."Button-Play_32.png";
		$FilePathStream = "user".DIRECTORY_SEPARATOR."Enigma_HTML".DIRECTORY_SEPARATOR."Button-Media-Player_32.png";
		$FilePathDelete = "user".DIRECTORY_SEPARATOR."Enigma_HTML".DIRECTORY_SEPARATOR."Button-Delete_32.png";
		$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/movielist");
		If ($xmlResult === false) {
			$this->SendDebug("GetMovieListUpdate", "Fehler beim Lesen der Movielist-Daten!", 0);
			$this->SetValue("e2movielist", "N/A");
			return;
		}
		If ($xmlResult->count() == 0) {
			$this->SendDebug("GetMovieListUpdate", "Keine Aufzeichnungs-Daten vorhanden", 0);
			$this->SetValue("e2movielist", "N/A");
			return;
		}
		$table = '<style type="text/css">';
		$table .= '<link rel="stylesheet" href="./.../webfront.css">';
		$table .= "</style>";
		$table .= '<table class="tg">';
		$table .= "<tr>";
		$table .= '<th class="tg-kv4b" align="left" width=150 >Titel</th>';
		If ($this->ReadPropertyBoolean("Movielist_Data_ShowShortDiscription") == true) {
			$table .= '<th class="tg-kv4b">Kurzbeschreibung<br></th>';
		}
		$table .= '<th class="tg-kv4b">Langbeschreibung<br></th>';
		If ($this->ReadPropertyBoolean("Movielist_Data_ShowSource") == true) {
			$table .= '<th class="tg-kv4b">Quelle</th>';
		}
		$table .= '<th class="tg-kv4b">Länge</th>';
		$table .= '<th class="tg-kv4b"></th>';
		If ($this->ReadPropertyBoolean("Movielist_Data_ShowMediaPlayer") == true) {
			$table .= '<th class="tg-kv4b"></th>';
		}
		$table .= '</tr>';
		for ($i = 0; $i <= count($xmlResult) - 1; $i++) {
			$Servicereference = (string)$xmlResult->e2movie[$i]->e2servicereference;
			$table .= '<tr>';
			$table .= '<td class="tg-611x">'.$xmlResult->e2movie[$i]->e2title.'</td>';
			If ($this->ReadPropertyBoolean("Movielist_Data_ShowShortDiscription") == true) {
				$table .= '<td class="tg-611x">'.$xmlResult->e2movie[$i]->e2description.'</td>';
			}
			$table .= '<td class="tg-611x">'.$xmlResult->e2movie[$i]->e2descriptionextended.'</td>';
			If ($this->ReadPropertyBoolean("Movielist_Data_ShowSource") == true) {
				$table .= '<td class="tg-611x">'.$xmlResult->e2movie[$i]->e2servicename.'</td>';
			}
			$table .= '<td class="tg-611x">'.$xmlResult->e2movie[$i]->e2length.'</td>';
			// Aufzeichnung im TV abspielen
			$table .= '<td class="tg-611x"><img src='.$FilePathPlay.' alt="Abspielen" 
				onclick="window.xhrGet=function xhrGet(o) {var HTTP = new XMLHttpRequest();HTTP.open(\'GET\',o.url,true);HTTP.send();};window.xhrGet({ url: \'hook/IPS2Enigma?Index='.$i.'&Source=Movielist_Play&SRef='.$Servicereference.'\' })"></td>';
			If ($this->ReadPropertyBoolean("Movielist_Data_ShowMediaPlayer") == true) {
				// Aufzeichnung aus dem Webfront streamen
				$MovieFilename = str_replace(" ", "%20", (string)$xmlResult->e2movie[$i]->e2filename);
				$Targetlink = "http://".$this->ReadPropertyString("IPAddress")."/web/ts.m3u?file=".$MovieFilename; 
				$table .= '<td class="tg-611x"><a href='.$Targetlink.' target="_blank"><img src='.$FilePathStream.' alt="Stream starten"></td>';
			}
			$table .= '</tr>';
		}
		$table .= '</table>';
		$this->SetValue("e2movielist", $table);
	}
	    
	private function GetEPGNowNextData()
	{
		// Erstellt eine HTML-Tabelle mit allen Sendern des ausgewählten Bouquets mit der jeweils aktuellen und der darauf folgenden Sendung
		If ($this->ReadPropertyBoolean("EPGlist_Data") == true) {
			$this->SendDebug("GetEPGNowNextData", "Ausfuehrung", 0);
			$FilePathStream = "user".DIRECTORY_SEPARATOR."Enigma_HTML".DIRECTORY_SEPARATOR."Button-Media-Player_32.png";
			$FilePathPlay = "user".DIRECTORY_SEPARATOR."Enigma_HTML".DIRECTORY_SEPARATOR."Button-Play_32.png";
			$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/getservices");
			If ($xmlResult === false) {
				$this->SendDebug("GetEPGNowNextData", "Fehler beim Lesen der Service-Referenz!", 0);
				$this->SetValue("e2epglistHTML", "N/A");
				return;
			}
			$bouquet = (string)$xmlResult->e2service[$this->ReadPropertyInteger("BouquetsNumber")]->e2servicereference;
			$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/epgnownext?bRef=".urlencode($bouquet));
			If ($xmlResult === false) {
				$this->SendDebug("GetEPGNowNextData", "Fehler beim Lesen der EPG-Daten!", 0);
				$this->SetValue("e2epglistHTML", "N/A");
				return;
			}
			If ($xmlResult->count() == 0) {
				$this->SendDebug("GetEPGNowNextData", "Keine EPG-Daten vorhanden", 0);
				$this->SetValue("e2epglistHTML", "N/A");
				return;
			}
			$table = '<style type="text/css">';
			$table .= '<link rel="stylesheet" href="./.../webfront.css">';
			$table .= "</style>";
			$table .= '<table class="tg">';
			$table .= "<tr>";
			$table .= '<th class="tg-kv4b" align="left" width=150 >Sender</th>';
			$table .= '<th class="tg-kv4b">Beginn<br></th>';
			$table .= '<th class="tg-kv4b">Titel</th>';
			If ($this->ReadPropertyBoolean("EPGlist_Data_ShowShortDiscription") == true) {
				$table .= '<th class="tg-kv4b">Kurzbeschreibung<br></th>';
			}
			$table .= '<th class="tg-kv4b">Dauer<br></th>';
			$table .= '<th class="tg-kv4b"></th>';
			If ($this->ReadPropertyBoolean("EPGlist_Data_ShowMediaPlayer") == true) {
				$table .= '<th class="tg-kv4b"></th>';
			}
			$table .= '<colgroup>'; 
			$table .= '<col width="120">'; 
			$table .= '<col width="100">'; 
			$table .= '</colgroup>';
			$table .= '</tr>';
			for ($i = 0; $i <= count($xmlResult) - 1; $i=$i+2) {
				$Servicereference = (string)$xmlResult->e2event[$i]->e2eventservicereference;
				$table .= '<tr>';
				$table .= '<td rowspan="2" class="tg-611x"><img src='.$this->Get_Filename($Servicereference).' alt='.(string)$xmlResult->e2event[$i]->e2eventservicename.' 
					onclick="window.xhrGet=function xhrGet(o) {var HTTP = new XMLHttpRequest();HTTP.open(\'GET\',o.url,true);HTTP.send();};window.xhrGet({ url: \'hook/IPS2Enigma?Index='.($i/2).'&Source=EPGlist_Data_A&SRef='.$Servicereference.'\' })"></td>';
				$table .= '<td class="tg-611x">'.date("H:i", (int)$xmlResult->e2event[$i]->e2eventstart).' Uhr'.'</td>';
				$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event[$i]->e2eventtitle).'</td>';
				If ($this->ReadPropertyBoolean("EPGlist_Data_ShowShortDiscription") == true) {
					$table .= '<td class="tg-611x" onclick="window.xhrGet=function xhrGet(o) {var HTTP = new XMLHttpRequest();HTTP.open(\'GET\',o.url,true);HTTP.send();};window.xhrGet({ url: \'hook/IPS2Enigma?Index='.($i/2).'&Source=EPGlist_Data_D\' })">'.utf8_decode($xmlResult->e2event[$i]->e2eventdescription).'</td>';			
				}
				$table .= '<td class="tg-611x">'.round((int)$xmlResult->e2event[$i]->e2eventduration / 60).' min'.'</td>';
				$table .= '</tr>';
				$table .= '<tr>';
				$table .= '<td class="tg-611x">'.date("H:i", (int)$xmlResult->e2event[$i+1]->e2eventstart).' Uhr'.'</td>';
				$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event[$i+1]->e2eventtitle).'</td>';
				If ($this->ReadPropertyBoolean("EPGlist_Data_ShowShortDiscription") == true) {
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event[$i+1]->e2eventdescription).'</td>';
				}
				$table .= '<td class="tg-611x">'.round((int)$xmlResult->e2event[$i+1]->e2eventduration / 60).' min'.'</td>';
				$table .= '<td class="tg-611x"><img src='.$FilePathPlay.' alt="Umschalten" 
					onclick="window.xhrGet=function xhrGet(o) {var HTTP = new XMLHttpRequest();HTTP.open(\'GET\',o.url,true);HTTP.send();};window.xhrGet({ url: \'hook/IPS2Enigma?Index='.($i/2).'&Source=EPGlist_Data_A&SRef='.$Servicereference.'\' })"></td>';
				$Targetlink = "http://".$this->ReadPropertyString("IPAddress")."/web/stream.m3u?ref=".urlencode((string)$xmlResult->e2event[$i]->e2eventservicereference)."&name=".urlencode((string)$xmlResult->e2event[$i]->e2eventservicename);
				If ($this->ReadPropertyBoolean("EPGlist_Data_ShowMediaPlayer") == true) {
					$table .= '<td class="tg-611x"><a href='.$Targetlink.' target="_blank"><img src='.$FilePathStream.' alt="Stream starten"></td>';
				}
				$table .= '</tr>';
			}
			$table .= '</table>';
			$this->SetValue("e2epglistHTML", $table);
		}
	}
	
	private function GetEPGNowNextDataSRef()   
	{
		// Erstellt eine HTML-Tabelle mit der aktuellen und den darauf folgenden Sendungen eines bestimmten Senders 
		If (($this->ReadPropertyBoolean("EPGlistSRef_Data") == true) AND (GetValueBoolean($this->GetIDForIdent("powerstate")) == true)) {
			$this->SendDebug("GetEPGNowNextDataSRef", "Ausfuehrung", 0);
			$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/subservices");
			If ($xmlResult === false) {
				$this->SendDebug("Get_EPGUpdate", "Fehler beim Lesen der EPG-Daten!", 0);
				return;
			}

			$e2servicereference = (string)$xmlResult->e2service->e2servicereference;
			$e2servicename = (string)$xmlResult->e2service->e2servicename;

			$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/epgservice?sRef=".$e2servicereference);
			If ($xmlResult === false) {
				$this->SendDebug("Get_EPGUpdate", "Fehler beim Lesen der EPG-Daten!", 0);
				return;
			}

			If ($xmlResult->count() == 0) {
				$this->SendDebug("Get_EPGUpdate", "Keine EPG-Daten vorhanden", 0);
				$this->SetValue("e2epglistSRefHTML", "N/A");
				return;
			}

			$table = '<style type="text/css">';
			$table .= '<link rel="stylesheet" href="./.../webfront.css">';
			$table .= "</style>";
			$table .= '<table class="tg">';
			$table .= "<tr>";
			$table .= '<th class="tg-kv4b" align="left" width=150 >Sender</th>';
			$table .= '<th class="tg-kv4b">Beginn<br></th>';
			$table .= '<th class="tg-kv4b">Titel</th>';
			If ($this->ReadPropertyBoolean("EPGlistSRef_Data_ShowShortDiscription") == true) {
				$table .= '<th class="tg-kv4b">Kurzbeschreibung<br></th>';
			}
			$table .= '<th class="tg-kv4b">Dauer<br></th>';
			$table .= '<colgroup>'; 
			$table .= '<col width="120">'; 
			$table .= '<col width="100">'; 
			$table .= '</colgroup>';
			$table .= '</tr>';
			$table .= '<tr>';
			$table .= '<td class="tg-611x"><img src='.$this->Get_Filename((string)$xmlResult->e2event[0]->e2eventservicereference).' alt='.(string)$xmlResult->e2event[0]->e2eventservicename.'></td>';
			$table .= '<td class="tg-611x">'.date("H:i", (int)$xmlResult->e2event[0]->e2eventstart).' Uhr'.'</td>';
			$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event[0]->e2eventtitle).'</td>';
			If ($this->ReadPropertyBoolean("EPGlistSRef_Data_ShowShortDiscription") == true) {
				$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event[0]->e2eventdescription).'</td>';
			}
			$table .= '<td class="tg-611x">'.round((int)$xmlResult->e2event[0]->e2eventduration / 60).' min'.'</td>';
			$table .= '</tr>';
			for ($i = 1; $i <= Min(count($xmlResult) - 1, 15); $i++) {
				$table .= '<tr>';
				$table .= '<td class="tg-611x"></td>';
				//$table .= '<td rowspan='.$ValueCount.' class="tg-611x"><img src='.$this->Get_Filename((string)$xmlResult->e2event[$i]->e2eventservicereference).' alt='.(string)$xmlResult->e2event[$i]->e2eventservicename.'></td>';
				$table .= '<td class="tg-611x">'.date("H:i", (int)$xmlResult->e2event[$i]->e2eventstart).' Uhr'.'</td>';
				$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event[$i]->e2eventtitle).'</td>';
				If ($this->ReadPropertyBoolean("EPGlistSRef_Data_ShowShortDiscription") == true) {
					$table .= '<td class="tg-611x">'.utf8_decode($xmlResult->e2event[$i]->e2eventdescription).'</td>';
				}
				$table .= '<td class="tg-611x">'.round((int)$xmlResult->e2event[$i]->e2eventduration / 60).' min'.'</td>';
				$table .= '</tr>';				
			}
			$table .= '</table>';
			$this->SetValue("e2epglistSRefHTML", $table);
		}
	}
	    
	private function Get_Powerstate()
	{
		$this->SendDebug("Get_Powerstate", "Ausfuehrung", 0);
		$result = GetValueBoolean($this->GetIDForIdent("powerstate"));
		
		$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/powerstate");
		If ($xmlResult === false) {
			$this->SendDebug("Get_Powerstate", "Fehler beim Lesen der Powerstate-Daten!", 0);
			return;
		}
		
		//$wert = $xml->e2instandby;
		If(strpos((string)$xmlResult->e2instandby, "false")!== false) {
			// Bei "false" ist die Box eingeschaltet
			SetValueBoolean($this->GetIDForIdent("powerstate"), true);
			$result = true;
		}
		else {
			SetValueBoolean($this->GetIDForIdent("powerstate"), false);
			$result = false;
		}
		
	return $result;
	}
	    
	public function ToggleStandby()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/powerstate?newstate=0");
			If ($xmlResult === false) {
				$this->SendDebug("ToggleStandby", "Fehler beim Setzen der Powerstate-Daten!", 0);
				return;
			}
		}
	}
	/*
	0 = Toogle Standby
	1 = Deepstandby
	2 = Reboot
	3 = Restart Enigma2
	4 = Wakeup from Standby
	5 = Standby
    	*/
	public function DeepStandby()
	{
	      	If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
		      	$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/powerstate?newstate=1");
			If ($xmlResult === false) {
				$this->SendDebug("DeepStandby", "Fehler beim Setzen der Powerstate-Daten!", 0);
				return;
			}
	      	}
	}
	
	public function Standby()
	{
	       	If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
		       	$xmlResult = $this->GetContent("http:///".$this->ReadPropertyString("IPAddress")."/web/powerstate?newstate=5");
			If ($xmlResult === false) {
				$this->SendDebug("Standby", "Fehler beim Setzen der Powerstate-Daten!", 0);
				return;
			}
	       	}
	}			       
	
	public function WakeUpStandby()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/powerstate?newstate=4");
			If ($xmlResult === false) {
				$this->SendDebug("WakeUpStandby", "Fehler beim Setzen der Powerstate-Daten!", 0);
				return;
			}
		}
	}
				       
	public function Reboot()
	{
	   	If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) { 
			$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/powerstate?newstate=2");
			If ($xmlResult === false) {
				$this->SendDebug("Reboot", "Fehler beim Setzen der Powerstate-Daten!", 0);
				return;
			}
		}
	}
	
	public function RestartEnigma()
	{
	      	If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/powerstate?newstate=3");
			If ($xmlResult === false) {
				$this->SendDebug("RestartEnigma", "Fehler beim Setzen der Powerstate-Daten!", 0);
				return;
			}
		}
	}		       
	public function GetCurrentServiceName()
	{
		$result = "";
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/subservices");
			If ($xmlResult === false) {
				$this->SendDebug("GetCurrentServiceName", "Fehler beim Lesen des Service!", 0);
				return $result;
			}
			else {
		       		$result = (string)$xmlResult->e2service[0]->e2servicename;
			}
		}
	return $result;
	}
	
	public function GetCurrentServiceReference()
	{
		$result = "";
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
	      		$xmlResult = $this->GetContent("http://".$this->ReadPropertyString("IPAddress")."/web/subservices");
			If ($xmlResult === false) {
				$this->SendDebug("GetCurrentServiceReference", "Fehler beim Lesen der Service-Referenz!", 0);
				return $result;
			}
			else {
		       		$result = (string)$xmlResult->e2service[0]->e2servicereference;
			}
		}
	return $result;
	}
    	
	public function WriteMessage(string $message, int $time)
	{
	   	$result = false;
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
		       $message = urlencode($message);
		       $xmlResult = new SimpleXMLElement(file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/web/message?text=".$message."&type=2&timeout=".$time));
		       if ($xmlResult->e2state == "True") {
		       		$result = true;
			}
		}
	return $result;
	}   
	
	public function WriteInfoMessage(string $message,int $time)
	{
	   	$result = false;
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
		       $message = urlencode($message);
		       $xmlResult = new SimpleXMLElement(file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/web/message?text=$message&type=1&timeout=$time"));
		       if ($xmlResult->e2state == "True") {
		       		$result = true;
			}
		}
	return $result;
	}  
	
	public function WriteAttentionMessage(string $message,int $time)
	{
	   	$result = false;
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
		       $message = urlencode($message);
		       $xmlResult = new SimpleXMLElement(file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/web/message?text=$message&type=3&timeout=$time"));
		       if ($xmlResult->e2state == "True") {
		       		$result = true;
			}
		}
	return $result;
	}
	
	public function Zap(string $servicereference)
	{
	   	$result = false;
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			$xmlResult = new SimpleXMLElement(file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/web/zap?sRef=".$servicereference));
		}
	}    
	    
	public function MoviePlay(string $servicereference)
	{
	   	$result = false;
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			$servicereference = urlencode($servicereference);
			$xmlResult = new SimpleXMLElement(file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/web/zap?sRef=".$servicereference));
		}
	}    
	
	public function ToggleMute()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			// 113 Key "mute"
			$xmlResult = new SimpleXMLElement(file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/web/remotecontrol?command=113"));
		}
	}    
	
	public function VolUp()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			// 115 Key "volume up"
			$xmlResult = new SimpleXMLElement(file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/web/remotecontrol?command=115"));
		}
	}        
	
	public function VolDown()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			// 114 Key "volume down"
			$xmlResult = new SimpleXMLElement(file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/web/remotecontrol?command=114"));
		}
	}          
	    
	public function GetScreenshot()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			$Content = file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/grab?format=jpg&r=".$this->ReadPropertyInteger("Screenshot"));
			IPS_SetMediaContent($this->GetIDForIdent("Screenshot_".$this->InstanceID), base64_encode($Content));  //Bild Base64 codieren und ablegen
			IPS_SendMediaEvent($this->GetIDForIdent("Screenshot_".$this->InstanceID)); //aktualisieren
		}
	} 
	
	private function RegisterMediaObject($Name, $Ident, $Typ, $Parent, $Position, $Cached, $Filename)
	{
		$MediaID = @$this->GetIDForIdent($Ident);
		if($MediaID === false) {
		    	$MediaID = 0;
		}
		
		if ($MediaID == 0) {
			 // Image im MedienPool anlegen
			$MediaID = IPS_CreateMedia($Typ); 
			// Medienobjekt einsortieren unter Kategorie $catid
			IPS_SetParent($MediaID, $Parent);
			IPS_SetIdent($MediaID, $Ident);
			IPS_SetName($MediaID, $Name);
			IPS_SetPosition($MediaID, $Position);
                    	IPS_SetMediaCached($MediaID, $Cached);
			$ImageFile = IPS_GetKernelDir()."media".DIRECTORY_SEPARATOR.$Filename;  // Image-Datei
			IPS_SetMediaFile($MediaID, $ImageFile, false);    // Image im MedienPool mit Image-Datei verbinden
		}  
	}     
	    
	private function ConnectionTest()
	{
	      $result = false;
	      If (Sys_Ping($this->ReadPropertyString("IPAddress"), 2000)) {
			//IPS_LogMessage("IPS2Enigma Netzanbindung","Angegebene IP ".$this->ReadPropertyString("IPAddress")." reagiert");
			$status = @fsockopen($this->ReadPropertyString("IPAddress"), 80, $errno, $errstr, 10);
				if (!$status) {
					$this->SendDebug("ConnectionTest", "Port ist geschlossen!", 0);
					IPS_LogMessage("IPS2Enigma Netzanbindung","Port ist geschlossen!");
					$this->SetStatus(202);
	   			}
	   			else {
	   				fclose($status);
					//IPS_LogMessage("IPS2Enigma Netzanbindung","Port ist geöffnet");
					$result = true;
					$this->SetStatus(102);
	   			}
		}
		else {
			$this->SendDebug("ConnectionTest", "IP ".$this->ReadPropertyString("IPAddress")." reagiert nicht!", 0);
			IPS_LogMessage("IPS2Enigma","IP ".$this->ReadPropertyString("IPAddress")." reagiert nicht!");
			$this->SetStatus(202);
		}
	return $result;
	}
	    
	private function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
	{
	        if (!IPS_VariableProfileExists($Name))
	        {
	            IPS_CreateVariableProfile($Name, 1);
	        }
	        else
	        {
	            $profile = IPS_GetVariableProfile($Name);
	            if ($profile['ProfileType'] != 1)
	                throw new Exception("Variable profile type does not match for profile " . $Name);
	        }
	        IPS_SetVariableProfileIcon($Name, $Icon);
	        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
	        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
	}
	
	public function GetServiceInformation()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			$xmlResult = new SimpleXMLElement(file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/web/getservices"));
			If (count($xmlResult->e2service) == 0) {
				$Result = "Es wurde nur kein Bouquet gefunden, bitte auf dem Receiver mindestens eines einrichten";
			}
			elseif (count($xmlResult->e2service) == 1) {
				$Result = "Es wurde nur ein Bouquet gefunden, die Einstellung muss daher 0 sein. (Aktuell: ".$this->ReadPropertyInteger("BouquetsNumber").")"; 
			}
			elseif (count($xmlResult->e2service) > 1) {
				$Result = "Es wurde folgende Bouquets gefunden:".chr(13);
				for ($i = 1; $i <= count($xmlResult->e2service) - 1; $i++) {
					$Result .= "Auswahl: ".$i." Bouquet: ".$xmlResult->e2service[$i]->e2servicename.chr(13);
				}
				$Result .= "Bitte die  Auswahl in das Feld Bouquet-Nummer eintragen. (Aktuell: ".$this->ReadPropertyInteger("BouquetsNumber").")";
			}
		}
	return $Result;
	}
	
	public function GetBouquetsInformation()
	{
		$Result = false;
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ConnectionTest() == true)) {
			
			$xmlResult = new SimpleXMLElement(file_get_contents("http://".$this->ReadPropertyString("IPAddress")."/web/getservices"));
			If ($xmlResult === false) {
				$this->SendDebug("GetBouquetsInformation", "Fehler beim Lesen der Daten!", 0);
				return $Result;
			}
			
			If (count($xmlResult->e2service) == 0) {
				Echo "Es wurde nur kein Bouquet gefunden, bitte auf dem Receiver mindestens eines einrichten";
			}
			else {
				$Result = array();
				for ($i = 0; $i <= count($xmlResult->e2service) - 1; $i++) {
					$Result[$i] = (string)$xmlResult->e2service[$i]->e2servicename;
				}
			}
		}
	return $Result;
	}    
	    
	private function RegisterHook($WebHook)
    	{
        	$ids = IPS_GetInstanceListByModuleID('{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}');
        	if (count($ids) > 0) {
            		$hooks = json_decode(IPS_GetProperty($ids[0], 'Hooks'), true);
            		$found = false;
            		foreach ($hooks as $index => $hook) {
                		if ($hook['Hook'] == $WebHook) {
                    			if ($hook['TargetID'] == $this->InstanceID) {
                        			return;
                    			}
                    			$hooks[$index]['TargetID'] = $this->InstanceID;
                    			$found = true;
                		}
            		}
            		if (!$found) {
                		$hooks[] = ['Hook' => $WebHook, 'TargetID' => $this->InstanceID];
            		}
            		IPS_SetProperty($ids[0], 'Hooks', json_encode($hooks));
            		IPS_ApplyChanges($ids[0]);
		}
        }
	
	protected function ProcessHookData() 
	{		
		if ((isset($_GET["Source"]) ) AND (isset($_GET["Index"])) ){
			
			$Source = $_GET["Source"];
			$Index = $_GET["Index"];
			$SRef = $_GET["SRef"];
			switch($Source) {
			case "EPGlist_Data_A":
			    	//IPS_LogMessage("IPS2Enigma","WebHookData - Source: ".$Source." Index: ".$Index);
				If (($this->ReadPropertyBoolean("Open") == true) AND ($this->Get_Powerstate() == true)) {
					// Spalte A					
					$this->Zap($SRef);
					$this->Get_EPGUpdate();
				}
				break;
			case "EPGlist_Data_D":
			    	IPS_LogMessage("IPS2Enigma","WebHookData - Source: ".$Source." Index: ".$Index);
				break;
			case "Movielist_Play":
				If (($this->ReadPropertyBoolean("Open") == true) AND ($this->Get_Powerstate() == true)) {
					$this->MoviePlay($SRef);
				}
				break;
			}
			
		}
	}
	    
	private function Get_Filename(string $sRef)
	{
		// aus der Service Referenz den Dateinamen des Picons generieren
		// Doppelpunkte durch Unterstriche ersetzen
		$Filename = str_replace(":", "_", $sRef);
		// das letzte Zeichen entfernen
		$Filename = substr($Filename, 0, -1);
		// .png anhängen
 		If ($this->ReadPropertyInteger("PiconSource") == 0) {
			$Filename = "user".DIRECTORY_SEPARATOR."Picons".DIRECTORY_SEPARATOR.$Filename.".png";
		}
		elseif ($this->ReadPropertyInteger("PiconSource") == 1) {
			$Filename = "user".DIRECTORY_SEPARATOR."Picons_Enigma".DIRECTORY_SEPARATOR.$Filename.".png";
		}
	return $Filename;
	}
	    
	private function SSH_Connect(String $Command)
	{
	        If (($this->ReadPropertyBoolean("Open") == true) ) {
			set_include_path(__DIR__.'/libs');
			require_once (__DIR__ . '/libs/Net/SSH2.php');
			
			$ssh = new Net_SSH2($this->ReadPropertyString("IPAddress"));
			$login = @$ssh->login($this->ReadPropertyString("User"), $this->ReadPropertyString("Password"));
			if ($login == false)
			{
			    	IPS_LogMessage("IPS2Enigma","SSH-Connect: Angegebene IP ".$this->ReadPropertyString("IPAddress")." reagiert nicht!");
				$this->SendDebug("SSH_Connect", "SSH-Connect: Angegebene IP ".$this->ReadPropertyString("IPAddress")." reagiert nicht!", 0);
			    	return false;
			}
			$Result = ""; //$ssh->exec($Command);
			$ssh->disconnect();
		}
		else {
			$result = "";
		}
	
        return $Result;
	}
	
	private function Get_HTML()
	{
		$WebfrontPath = IPS_GetKernelDir()."webfront".DIRECTORY_SEPARATOR."user".DIRECTORY_SEPARATOR."Enigma_HTML";
		$SourcePath = IPS_GetKernelDir()."modules".DIRECTORY_SEPARATOR."IPS2Enigma".DIRECTORY_SEPARATOR."IPS2Enigma".DIRECTORY_SEPARATOR."imgs".DIRECTORY_SEPARATOR."HTML";
		if (file_exists($WebfrontPath)) {
			// Das Verzeichnis existiert bereits
		} 
		else {
			//Das Verzeichnis existiert nicht
			$result = mkdir($WebfrontPath);
			If (!$result) {
				IPS_LogMessage("IPS2Enigma","Fehler bei der Verzeichniserstellung!");
				$this->SendDebug("Get_HTML", "Fehler bei der Verzeichniserstellung!", 0);
			}
		}
		$Path = opendir($SourcePath);
		while (false !== ($file = readdir($Path)))
		{
			if ($file != "." && $file != "..") {
				copy($SourcePath.DIRECTORY_SEPARATOR.$file, $WebfrontPath.DIRECTORY_SEPARATOR.$file); // Datei kopieren
			}
		}
		closedir($Path);	
	}
	    
	public function Get_Picons_Enigma()
	{
	        $PiconUpdate = $this->ReadPropertyBoolean("PiconUpdate");
		$result = false;
		If ($PiconUpdate == true) {
			If (($this->ReadPropertyBoolean("Open") == true) ) {
				// Prüfen, ob das Verzeichnis schon existiert
				$WebfrontPath = IPS_GetKernelDir()."webfront".DIRECTORY_SEPARATOR."user".DIRECTORY_SEPARATOR."Picons_Enigma";
				$SourcePath = "/usr/share/enigma2/picon";
				if (file_exists($WebfrontPath)) {
					// Das Verzeichnis existiert bereits
				} else {
					//Das Verzeichnis existiert nicht
					$result = mkdir($WebfrontPath);
					If (!$result) {
						IPS_LogMessage("IPS2Enigma","Fehler bei der Verzeichniserstellung!");
						$this->SendDebug("Get_Picons_Enigma", "Fehler bei der Verzeichniserstellung!", 0);
					}
				}

				$ftp_server = $this->ReadPropertyString("IPAddress");
				$ftp_user_name = $this->ReadPropertyString("User");
				$ftp_user_pass = $this->ReadPropertyString("Password");
				// set up basic connection
				$conn_id = ftp_connect($ftp_server);
				// login with username and password
				$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
				If ($login_result == true) {
					ftp_chdir($conn_id, $SourcePath);
					// die Dateien in diesem Verzeichnis ermitteln
					$contents = ftp_nlist($conn_id, ".");
					for ($i = 0; $i <= count($contents) - 1; $i++) {
						$result = ftp_get ($conn_id, $WebfrontPath.DIRECTORY_SEPARATOR.$contents[$i], $SourcePath.DIRECTORY_SEPARATOR.$contents[$i], FTP_BINARY);
						If (!$result) {
							IPS_LogMessage("IPS2Enigma","Fehler beim Kopieren der Datei ".$contents[$i]."!");
							$this->SendDebug("Get_Picons_Enigma", "Fehler beim Kopieren der Datei ".$contents[$i]."!", 0);
						}

					}
					$result = true;
				}
				else {
					IPS_LogMessage("IPS2Enigma","Fehler bei der Verbindung!");
					$this->SendDebug("Get_Picons_Enigma", "Fehler bei der Verbindung!", 0);
					$result = false;
				}
				// close the connection
				ftp_close($conn_id); 
			}
			else {
				$result = false;
			}
		}
		else {
			$this->SendDebug("Get_Picons_Enigma", "Picon Update wurde nicht durchgefuehrt", 0);
		}
        return $result;
	}   
	    
	private function Get_Picons()
	{
		$PiconUpdate = $this->ReadPropertyBoolean("PiconUpdate");
		If ($PiconUpdate == true) {
			// Quelldatei
			$FileName = IPS_GetKernelDir()."modules".DIRECTORY_SEPARATOR."IPS2Enigma".DIRECTORY_SEPARATOR."IPS2Enigma".DIRECTORY_SEPARATOR."imgs".DIRECTORY_SEPARATOR."Picons.zip";
			// Zielpfad
			$WebfrontPath = IPS_GetKernelDir()."webfront".DIRECTORY_SEPARATOR."user".DIRECTORY_SEPARATOR;  
			if (file_exists($FileName)) {
				// Prüfen, ob die Datei neuer ist, als die bisher installierte
				If (filemtime($FileName) > GetValueInteger($this->GetIDForIdent("PiconUpdate"))) {
					$zip = new ZipArchive;
					if ($zip->open($FileName) === TRUE) {
					$zip->extractTo($WebfrontPath);
					$zip->close();
						// Neues Erstellungsdatum der Datei sichern
						SetValueInteger($this->GetIDForIdent("PiconUpdate"), filemtime($FileName));
						$this->SendDebug("Get_Picons", "Picon Update erfolgreich", 0);
					} 
					else {
						IPS_LogMessage("IPS2Enigma","Picon Update nicht erfolgreich!");
						$this->SendDebug("Get_Picons", "Picon Update nicht erfolgreich!", 0);
					}
				}
			}
		}
		else {
			$this->SendDebug("Get_Picons", "Picon Update wurde nicht durchgefuehrt", 0);
		}
	}
				       
/*	    
//*************************************************************************************************************
// Schreibt eine Message auf den Bildschirm die man mit ja oder nein beantworten muss
// man sollte die Frage immer so stellen, das nein als aktive Antwort ausgewertet wird,
// da in allen anderen Fällen 0 oder -1  gemeldet wird
// return
// -1  wenn keine erfolgreiche Verbindung
// 0 wenn mit ja oder garnicht geantwortet wurde
// 1 wenn mit nein geantwortet
function ENIGMA2_GetAnswerFromMessage($ipadr,$message = "",$time=5)
{
    $type = 0;
    $result = -1;
   if (ENIGMA2_GetAvailable( $ipadr ))
    {
       $message = urlencode($message);
       $xmlResult = new SimpleXMLElement(file_get_contents("http://$ipadr/web/message?text=$message&type=$type&timeout=$time"));
      if ($xmlResult->e2state == "True")
      {
         sleep($time);
         $result = -1;
         $xmlResult =  new SimpleXMLElement(file_get_contents("http://$ipadr/web/messageanswer?getanswer=now"));
            if ($xmlResult->e2statetext == "Answer is NO!")
          {
              $result = 1;
          }
          else
          {
             $result = 0;
          }
        }    }
   else
    {
       $result = -1;
    }
return $result;
}
//*************************************************************************************************************
// Prüft ob die Box gerade aufnimmt
function ENIGMA2_RecordStatus($ipadr)
{
   $result = false;
echo "test";
	if (ENIGMA2_GetAvailable( $ipadr ))
    	{
		$xml = simplexml_load_file("http://$ipadr/web/recordnow?.xml");
echo $xml;
		$wert = $xml->e2state;
		echo $wert;
		if(strpos($wert,"false")!== false)
			{
			$result = true; // Bei "false" ist die Box eingeschaltet
			}
		else
			{
			$result = false;
			}
		}
		else
		   {
		   Echo "Box nicht erreichbar";
		   }
return $result;
}
*/
}
?>

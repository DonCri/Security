<?
		
    // Klassendefinition
    class Alarmanlage extends IPSModule {
        
        // Der Konstruktor des Moduls
        // Überschreibt den Standard Kontruktor von IPS
        public function __construct($InstanceID) {
            // Diese Zeile nicht löschen
            parent::__construct($InstanceID);


            // Selbsterstellter Code
        }
        
        // Überschreibt die interne IPS_Create($id) Funktion
	public function Create() {

            // Diese Zeile nicht löschen.
            parent::Create();

            //Profil für Modusauswahl
            if (!IPS_VariableProfileExists("BRELAG.AlarmModus")) {
        			IPS_CreateVariableProfile("BRELAG.AlarmModus", 1);
        			IPS_SetVariableProfileValues("BRELAG.AlarmModus", 0, 5, 0);
        			IPS_SetVariableProfileIcon("BRELAG.AlarmModus", "IPS");
        			IPS_SetVariableProfileAssociation("BRELAG.AlarmModus", 0, $this->Translate("ModeOne"), "", -1);
					IPS_SetVariableProfileAssociation("BRELAG.AlarmModus", 1, $this->Translate("ModeBell"), "", -1);
        		}

            //Progil für Quittierung
            if (!IPS_VariableProfileExists("BRELAG.Quittierung")) {
        			IPS_CreateVariableProfile("BRELAG.Quittierung", 1);
        			IPS_SetVariableProfileValues("BRELAG.Quittierung", 0, 4, 0);
        			IPS_SetVariableProfileIcon("BRELAG.Quittierung", "IPS");
        			IPS_SetVariableProfileAssociation("BRELAG.Quittierung", 0, "Alarmmeldung", "", -1);
        			IPS_SetVariableProfileAssociation("BRELAG.Quittierung", 1, "Sabotage", "", -1);
        			IPS_SetVariableProfileAssociation("BRELAG.Quittierung", 2, "Batterie", "", -1);
					IPS_SetVariableProfileAssociation("BRELAG.Quittierung", 3, "Lebensdauer", "", -1);
        		}

            // Profil für Statusanzeige
            if(!IPS_VariableProfileExists("BRELAG.AlarmStatus")) {
        			IPS_CreateVariableProfile("BRELAG.AlarmStatus", 0);
        			IPS_SetVariableProfileIcon("BRELAG.AlarmStatus", "Power");
        			IPS_SetVariableProfileAssociation("BRELAG.AlarmStatus", 0, $this->Translate("Off"), "", -1);
        			IPS_SetVariableProfileAssociation("BRELAG.AlarmStatus", 1, $this->Translate("On"), "", -1);
            }
            
            // Eigenschaften für Formular
            $this->RegisterPropertyString("Supplement", "[]"); // Liste für boolean Variablen (z.B. Magnetkontakt -> Status). Können auch andere Variablen sein, solange es sich um Boolsche handelt.
            $this->RegisterPropertyInteger("WebFrontName", 0); // Integer Wert für WebFront Auswahl. Wird für die Push-Nachrichten benötigt
            $this->RegisterPropertyString("PushTitel1", ""); // Titel welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("PushText1", ""); // Test welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("AlertSound1", ""); // Wählbare Alarm Sounds für Mobilgeräte (siehe Liste von Symcon
            $this->RegisterPropertyString("PushTitel2", ""); // Titel welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("PushText2", ""); // Test welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("AlertSound2", ""); // Wählbare Alarm Sounds für Mobilgeräte (siehe Liste von Symcon

	    	$this->RegisterPropertyString("SabotageID", "[]"); // Liste für Variablen
			$this->RegisterPropertyString("Nachricht1", "Status");
			$this->RegisterPropertyString("Nachricht2", "Ereignis");
	    
	    
            
            // Boolean für Statusanzeige der Alarmanlage, ist inaktiv!
            $this->RegisterVariableBoolean("State", "Status", "BRELAG.AlarmStatus", "0");
            
            // Zeigt der Letzte Alarm im Array (Zeigt nur der letzte Wert vom Array)
            $this->RegisterVariableString("LastAlert", "Letzter Alarm", "", "0");

            // Stringvariable für Passwort Eingabe um Anlage scharf bzw. unschaf zu schalten, ist aktiv!
            $this->RegisterVariableString("Password", "Passwort Eingabe", "", "1");
            $this->EnableAction("Password");           

            // Integervariable für Auswahl der Quittierungen, ist aktiv!
            $this->RegisterVariableInteger("Quittierung", "Quittierung", "BRELAG.Quittierung", "3");
            $this->EnableAction("Quittierung");

            // Stringvariable für ändern des Passworts, Variable "Neues Passwort" verborgen aber beide aktiv!
            $this->RegisterVariableString("OldPassword", "Passwort ändern (aktuelles Password eingeben)", "", "4");
            $this->EnableAction("OldPassword");
            $this->RegisterVariableString("NewPassword", "Neues Passwort", "", "5");
            $this->EnableAction("NewPassword");
            IPS_SetHidden($this->GetIDForIdent("NewPassword"), true);

	    	$this->RegisterVariableInteger("MagnetAlarm", "Alarmauslösung", "", "10");
	    	$this->RegisterVariableInteger("SabotageAlarm", "SabotageAlarm", "", "11");


            

            // Test Variablen
                      
        }


        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function RequestAction($Ident, $Value) {

              switch($Ident) {
                    case "Password":
                    //Neuen Wert in die Statusvariable schreiben
                      	SetValue($this->GetIDForIdent($Ident), $Value);
                      	$this->Activate();
                    break;
                    
                    case "Quittierung":
                      //Neuen Wert in die Statusvariable schreiben
						SetValue($this->GetIDForIdent($Ident), $Value);
						$this->Quittierung();
                    break;
                    
                    case "OldPassword":
                        //Neuen Wert in die Statusvariable schreiben
                        SetValue($this->GetIDForIdent($Ident), $Value);
                        $this->NewPassword();
                    break;
                    
                    case "NewPassword":
                        //Neuen Wert in die Statusvariable schreiben
                        SetValue($this->GetIDForIdent($Ident), $Value);
                    break;
                    }

      }
	
	public function Quittierung() {

			$AlarmState = GetValue($this->GetIDForIdent("State"));
			$AlarmQuittierung = GetValue($this->GetIDForIdent("Quittierung"));

			switch ($AlarmState)
            {
            	case false:
	    	        switch ($AlarmQuittierung)
    	    	    {
			            case 0:
							SetValue($this->GetIDForIdent("LastAlert"), "");
							SetValue($this->GetIDForIdent("MagnetAlarm"), 0);
						break;
                                    
						case 1:
							SetValue($this->GetIDForIdent("SabotageAlarm"), 0);
						break;
                                    
						case 2:
								
						break;
                                    
						case 3:		
								
						break;
					}
                                // Platzhalter für Quittierfunktion
				break;
                                
				default:
					echo "Alarm deaktivieren";
				break;
			}
	}
      
	public function Activate() {

            $Password = GetValue($this->GetIDForIdent("Password"));
            $currentPassword = GetValue($this->GetIDForIdent("NewPassword"));
            $State = GetValue($this->GetIDForIdent("State"));


            if($Password == $currentPassword && $State == false)
            {
                SetValue($this->GetIDForIdent("State"), true);
                SetValue($this->GetIDForIdent("Password"), "");
            } elseif($Password == $currentPassword && $State == true)
              {
                SetValue($this->GetIDForIdent("State"), false);
                SetValue($this->GetIDForIdent("Password"), "");
              } elseif ($Password != $currentPassword)
              {
                  SetValue($this->GetIDForIdent("Password"), "");
                  echo "Falsches Passwort, versuch es nochmals";
              }
              
        }
        
        
	public function NewPassword() {


          $Password = GetValue($this->GetIDForIdent("OldPassword"));
          $NewPassword = GetValue($this->GetIDForIdent("NewPassword"));
          $State = GetValue($this->GetIDForIdent("State"));

          if($Password == $NewPassword && $State == false)
          {
            SetValue($this->GetIDForIdent("OldPassword"), "");
            SetValue($this->GetIDForIdent("NewPassword"), "");
            IPS_SetHidden($this->GetIDForIdent("NewPassword"), false);
            IPS_Sleep(15000);
            IPS_SetHidden($this->GetIDForIdent("NewPassword"), true);
            
          } else
            {
                SetValue($this->GetIDForIdent("OldPassword"), "");
                echo "ACHTUNG: Falsches Passwort oder Anlage noch aktiv";                
            }
          

        }

       
        public function StateCheck() {
           
          $array = json_decode($this->ReadPropertyString("Supplement"));
          
          $AlarmAktiv = GetValue($this->GetIDForIdent("State"));
          $Titel1 = $this->ReadPropertyString("PushTitel1");
          $Text1 = $this->ReadPropertyString("PushText1");
		  $AlertSound1 = $this->ReadPropertyString("AlertSound1");
		  $Titel2 = $this->ReadPropertyString("PushTitel2");
          $Text2 = $this->ReadPropertyString("PushText2");
          $AlertSound2 = $this->ReadPropertyString("AlertSound2");


		  foreach($array as $arrayID)
		  {
				  $VariableName = IPS_GetName($arrayID->ID);
				  $VariableState = GetValue($arrayID->ID);
				  $InstanzID = IPS_GetParent($arrayID->ID);
                  $InstanzName = IPS_GetName($InstanzID);   
			      $VariableInfo = IPS_GetVariable($arrayID->ID);
				  $DiffToLastChange = strtotime("now") - $VariableInfo["VariableChanged"];

				  switch($VariableName)
				  {
				  	case $this->ReadPropertyString("Nachricht1"):
							switch($AlarmAktiv)
							{
								case true:
									if($VariableState == true && $DiffToLastChange <= 10)
                                	{    
                                    SetValue($this->GetIDforIdent("LastAlert"), $InstanzName);
                                    
                                    WFC_PushNotification($this->ReadPropertyInteger("WebFrontName"), "$Titel1", "$InstanzName $Text1", "$AlertSound1", $InstanzID);
				    				WFC_SendPopup($this->ReadPropertyInteger("WebFrontName"), "$Titel1", "$InstanzName $Text1");
				    				SetValue($this->GetIDForIdent("MagnetAlarm"), 1);
                                    
									}
								break;
							}
					break;

					case $this->ReadPropertyString("Nachricht2"):
                            if($VariableState == true && $DiffToLastChange <= 10)
                                	{    
                                    SetValue($this->GetIDforIdent("LastAlert"), $InstanzName);
                                    
                                    WFC_PushNotification($this->ReadPropertyInteger("WebFrontName"), "$Titel2", "$InstanzName $Text2", "$AlertSound2", $InstanzID);
				    				WFC_SendPopup($this->ReadPropertyInteger("WebFrontName"), "$Titel2", "$InstanzName $Text2");
				    				SetValue($this->GetIDForIdent("SabotageAlarm"), 1);                                    
									}
					break;

				  }
				 
		  }
          
		  /*
          switch($AlarmStatus)
          {
              case true: // Alarm eingeschaltet
                   
                        foreach ($array as $StatusID) 
                            {
                            $Status = GetValue($StatusID->ID);
							$VariableName = IPS_GetName($StatusID->ID);
                            $InstanzID = IPS_GetParent($StatusID->ID);
                            $InstanzName = IPS_GetName($InstanzID);   
			    			$VariableInfo = IPS_GetVariable($StatusID->ID);
			    			$DiffToLastChange = strtotime("now") - $VariableInfo["VariableChanged"];
                    
                            if($Status == true && $DiffToLastChange <= 10)
                                {    
                                    SetValue($this->GetIDforIdent("LastAlert"), $InstanzName);
                                    
                                    WFC_PushNotification($this->ReadPropertyInteger("WebFrontName"), "$Titel", "$InstanzName $Text", "$AlertSound", $InstanzID);
				    				WFC_SendPopup($this->ReadPropertyInteger("WebFrontName"), "$Titel", "$InstanzName $Text");
				    				SetValue($this->GetIDForIdent("MagnetAlarm"), 1);
                                    
                                }
                               
                            }
                    
                        foreach ($array as $StatusID)
                        {
                            $Status = GetValue($StatusID->ID);
                            $InstanzID = IPS_GetParent($StatusID->ID);
                            $InstanzName = IPS_GetName($InstanzID->ID);
                            $VariableInfo = IPS_GetVariable($StatusID->ID);
			    			$DiffToLastChange = strtotime("now") - $VariableInfo["VariableChanged"];
                    
                            if($Status == true && $DiffToLastChange <= 10)
                                {    
                                    SetValue($this->GetIDforIdent("LastAlert"), $InstanzName);
                                    
                                    WFC_PushNotification($this->ReadPropertyInteger("WebFrontName"), "$Titel", "$InstanzName $Text", "$AlertSound", $InstanzID);
                                    WFC_SendPopup($this->ReadPropertyInteger("WebFrontName"), "$Titel", "$InstanzName $Text");
                                    
                                }
                            
                        }
                    
              break;  
		  }
		   */
   
        }

	public function CheckSabotage() {
	
		$arraySabID = json_decode($this->ReadPropertyString("SabotageID"));
		foreach($arraySabID as $SaboActivate) {
			$ID = GetValue($SaboActivate->SabotageVariablen);
			switch($ID)
			{
				case 1:
					SetValue($this->GetIDForIdent("SabotageAlarm"), 1);
				break;
			}
		}	
	
	
	}
        
        
        public function AlarmQuittierung() {
            
        }
        
        public function ApplyChanges() {
            
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
            
            $StateUpdate = json_decode($this->ReadPropertyString("Supplement"));
            foreach ($StateUpdate as $IDUpdate) {
                $this->RegisterMessage($IDUpdate->ID, VM_UPDATE);
	    	}
            
        }
        
        public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
            
            $this->SendDebug("MessageSink", "SenderID: ". $SenderID .", Message: ". $Message , 0);
            $ID = json_decode($this->ReadPropertyString("Supplement"));

            foreach ($ID as $state) {
                        $this->StateCheck();
                    
                    return;
	    	}
            
        }
        

    }
?>

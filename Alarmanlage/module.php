<?
    // Klassendefinition
    class Alarmanlage extends IPSModule {
        
        
        public $UpdateTime = 15000;
        
        
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
            $this->RegisterPropertyString("Supplement", "[]"); // Liste für boolean Variablen (z.B. Magnetkontakt -> Status)
            $this->RegisterPropertyInteger("WebFrontName", 0); // Integer Wert für WebFront Auswahl. Wird für die Push-Nachrichten benötigt
            $this->RegisterPropertyString("PushTitel", ""); // Titel welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("PushText", ""); // Test welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("AlertSound", ""); // Wählbare Alarm Sounds für Mobilgeräte (siehe Liste von Symcon
             
            // Boolean für Statusanzeige der Alarmanlage, ist inaktiv!
            $this->RegisterVariableBoolean("State", "Status", "BRELAG.AlarmStatus", "0");
            
            // Zeigt der Letzte Alarm im Array (Zeigt nur der letzte Wert vom Array)
            $this->RegisterVariableString("LastAlert", "Letzter Alarm", "~TextBox", "0"); 
            
            // Setzt einen Timer für den Status check der Magnetkontakt Variablen
            $this->RegisterTimer("StatusCheck", $this->UpdateTime, 'MW_StateCheck($_IPS[\'TARGET\']);');

            // Stringvariable für Passwort Eingabe um Anlage scharf bzw. unschaf zu schalten, ist aktiv!
            $this->RegisterVariableString("Password", "Passwort Eingabe", "", "1");
            $this->EnableAction("Password");           

            // Integervariable für Auswahl der Modi, ist aktiv!
            $this->RegisterVariableInteger("Mode", "Modus", "BRELAG.AlarmModus", "2");
            $this->EnableAction("Mode");

            // Integervariable für Auswahl der Quittierungen, ist aktiv!
            $this->RegisterVariableInteger("Quittierung", "Sabotage", "BRELAG.Quittierung", "3");
            $this->EnableAction("Quittierung");

            // Stringvariable für ändern des Passworts, Variable "Neues Passwort" verborgen aber beide aktiv!
            $this->RegisterVariableString("OldPassword", "Passwort ändern", "", "4");
            $this->EnableAction("OldPassword");
            $this->RegisterVariableString("NewPassword", "Neues Passwort", "", "5");
            $this->EnableAction("NewPassword");
            IPS_SetHidden($this->GetIDForIdent("NewPassword"), true);

            

            // Test Variablen
                      
        }


        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function RequestAction($Ident, $Value) {

            $AlarmState = GetValue($this->GetIDForIdent("State"));
            $AlarmQuittierung = GetValue($this->GetIDForIdent("Quittierung"));
            
            
              switch($Ident) {
                    case "Password":
                    //Neuen Wert in die Statusvariable schreiben
                      SetValue($this->GetIDForIdent($Ident), $Value);
                      $this->Activate();
                    break;
                    
                    case "Mode":
                      //Neuen Wert in die Statusvariable schreiben                      
                      
                            switch ($AlarmState)
                            {
                                case false:
                                    switch($Value)
                                    {
                                        case 0:
                                            $this->UpdateTime = 15000;
                                        break;
                                        
                                        case 1:
                                            $this->UpdateTime = 1000;
                                        break;
                                    }
                                break;
                                
                                default:
                                    echo "Alarm deaktivieren";
                                break;
                            }
                            SetValue($this->GetIDForIdent($Ident), $Value);
                      
                    break;
                    
                    case "Quittierung":
                      //Neuen Wert in die Statusvariable schreiben
                        
                        switch ($AlarmState)
                        {
                            case false:
                                switch ($AlarmQuittierung)
                                {
                                    case 0:
                                        SetValue($this->GetIDForIdent("LastAlert"), "");
                                    break;
                                    
                                    case 1:
                                        
                                    break;
                                    
                                    case 2:
                                        
                                    break;
                                    
                                    case 3:
                                        
                                    break;
                                }
                                SetValue($this->GetIDForIdent($Ident), $Value);
                                // Platzhalter für Quittierfunktion
                                break;
                                
                            default:
                                echo "Alarm deaktivieren";
                            break;
                        }
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
                echo "ACHTUNG: Falsches Passwort und / oder Anlage noch aktiv";                
            }
          

        }

       
        public function StateCheck() {
           
          $array = json_decode($this->ReadPropertyString("Supplement"), true);
          
          $AlarmModus = GetValue($this->GetIDForIdent("Mode"));
          $AlarmStatus = GetValue($this->GetIDForIdent("State"));
          $Titel = $this->ReadPropertyString("PushTitel");
          $Text = $this->ReadPropertyString("PushText");
          $AlertSound = $this->ReadPropertyString("AlertSound");
          
          
          switch($AlarmStatus)
          {
              case true: // Alarm eingeschaltet
                  
                switch($AlarmModus)
                    { 
                    case 0: // Normaler Modus
                   
                        foreach ($array as $StatusIDstring) 
                            {
                            $StatusID = implode($StatusIDstring);
                            $Status = GetValue($StatusID);
                            $InstanzID = IPS_GetParent($StatusID);
                            $InstanzName = IPS_GetName($InstanzID);        
                    
                            if($Status == true)
                                {                             
                                    
                                    SetValue($this->GetIDforIdent("LastAlert"), $InstanzName);
                                    WFC_PushNotification($this->ReadPropertyInteger("WebFrontName"), "$Titel", "$InstanzName $Text", "$AlertSound", $InstanzID);
                                    WFC_SendPopup($this->ReadPropertyInteger("WebFrontName"), "$Titel", "$InstanzName $Text");
                                    
                                }
                               
                            }
                    break;
                  
                    } 
                    
              break;  
          }
   
        }
        
        
        public function AlarmQuittierung() {
            
        }
        

    }
?>

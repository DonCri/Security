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
        			IPS_SetVariableProfileAssociation("BRELAG.AlarmModus", 1, $this->Translate("ModeTwo"), "", -1);
        			IPS_SetVariableProfileAssociation("BRELAG.AlarmModus", 2, $this->Translate("ModeOneTwo"), "", -1);
        			IPS_SetVariableProfileAssociation("BRELAG.AlarmModus", 3, $this->Translate("ModeBell"), "", -1);
        		}

            //Progil für Quittierung
            if (!IPS_VariableProfileExists("BRELAG.Quittierung")) {
        			IPS_CreateVariableProfile("BRELAG.Quittierung", 1);
        			IPS_SetVariableProfileValues("BRELAG.Quittierung", 0, 3, 0);
        			IPS_SetVariableProfileIcon("BRELAG.Quittierung", "IPS");
        			IPS_SetVariableProfileAssociation("BRELAG.Quittierung", 0, "Sabotage", "", -1);
        			IPS_SetVariableProfileAssociation("BRELAG.Quittierung", 1, "Batterie", "", -1);
        			IPS_SetVariableProfileAssociation("BRELAG.Quittierung", 2, "Lebensdauer", "", -1);
        		}

            // Profil für Statusanzeige
            if(!IPS_VariableProfileExists("BRELAG.AlarmStatus")) {
        			IPS_CreateVariableProfile("BRELAG.AlarmStatus", 0);
        			IPS_SetVariableProfileIcon("BRELAG.AlarmStatus", "Power");
        			IPS_SetVariableProfileAssociation("BRELAG.AlarmStatus", 0, $this->Translate("Off"), "", -1);
        			IPS_SetVariableProfileAssociation("BRELAG.AlarmStatus", 1, $this->Translate("On"), "", -1);
            }
            // Boolean für Statusanzeige der Alarmanlage, ist inaktiv!
            $this->RegisterVariableBoolean("State", "Status", "BRELAG.AlarmStatus", "0");

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
            $this->RegisterVariableString("OldPassword", "Aktuelles Passwort", "", "4");
            $this->EnableAction("OldPassword");
            $this->RegisterVariableString("NewPassword", "Neues Passwort", "", "5");
            $this->EnableAction("NewPassword");
            IPS_SetHidden($this->GetIDForIdent("NewPassword"), true);

            // Eigenschaften für Formular
            $this->RegisterPropertyString("Supplement", "[]");
            $this->RegisterPropertyString("ID", "[]");


            // Test Variable
            $this->RegisterVariableString("TestString", "TestString", "", "0");
            $this->EnableAction("TestString");
            
            $this->RegisterVariableBoolean("TestBoolean", "TestBoolean", "", "0");
            $this->EnableAction("TestBoolean");
        }


        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function RequestAction($Ident, $Value) {

              switch($Ident) {
                    case "Password":
                    //Neuen Wert in die Statusvariable schreiben
                      SetValue($this->GetIDForIdent($Ident), $Value);
                      $this->Activate();
                    break;
                    case "Mode":
                      //Neuen Wert in die Statusvariable schreiben
                      SetValue($this->GetIDForIdent($Ident), $Value);
                    break;
                    case "Quittierung":
                      //Neuen Wert in die Statusvariable schreiben
                        SetValue($this->GetIDForIdent($Ident), $Value);
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

        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * ABC_MeineErsteEigeneFunktion($id);
        *
        */
        public function Activate() {
            $Password = GetValue($this->GetIDForIdent("Password"));
            $NewPassword = GetValue($this->GetIDForIdent("NewPassword"));
            $State = GetValue($this->GetIDForIdent("State"));
            $currentPassword = "";


            if($Password == $currentPassword && $State == false)
            {
                SetValue($this->GetIDForIdent("State"), true);
                SetValue($this->GetIDForIdent("Password"), "");
            } elseif($Password == $currentPassword && $State == true)
              {
                SetValue($this->GetIDForIdent("State"), false);
                SetValue($this->GetIDForIdent("Password"), "");
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
            
          }
          

        }

        public function StateCheck() {
           
          $array = json_decode($this->ReadPropertyString("Supplement"), true);
           
          
          foreach ($array as $StatusID) 
          {
              $State = intval($StatusID);
              
              if($State = true)
              {
                  echo "Alarm";
              }
          }
          
          
          
            SetValue($this->GetIDForIdent("TestBoolean"), GetValue(implode($array[0])) );
            
        }
        

    }
?>

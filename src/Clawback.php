<?php
namespace NovakSolutions\Infusionsoft;

class Clawback extends Base {

    protected static $tableFields = array(
        "Id", //This is non-numeric
        "Description",
        "Amount",
        "InvoiceId",
        "FirstName",
        "ProductName",
        "ContactId",
        "SoldByFirstName",
        "DateEarned",
        "SaleAffId",
    );

    //Clawbacks don't actually have ids in Infusionsoft, so $idString is i of the form $affId/$date/$index
    public function __construct($idString = null, $app = null){
        $this->table = 'Clawback';
        if (is_array($idString)){
            $this->loadFromArray($idString);
        }
        if ($idString != null) {
            $this->load($idString, $app);
        }
    }

    public function getFields(){
        return self::$tableFields;
    }

    public function addCustomField($name){
        self::$tableFields[] = $name;
    }

    public function addCustomFields($fields){
        foreach($fields as $name){
            self::addCustomField($name);
        }
    }

    public function removeField($fieldName){
        $fieldIndex = array_search($fieldName, self::$tableFields);
        if($fieldIndex !== false){
            unset(self::$tableFields[$fieldIndex]);
            self::$tableFields = array_values(self::$tableFields);
        }
    }

    public function save() {
        throw new Exception("Commissions cannot be saved");
    }

    public function load($idString, $app = null) {
        //parse $idString
        $this->Id = $idString;
        $idArray = explode('/', $idString);
        $affiliateId = $idArray[0];
        $invoiceId = $idArray[1];
        $dateString = $idArray[2];
        $index = $idArray[3];

        $date = DateTime::createFromFormat(Service::apiDateFormat, $dateString);
        $dateString = $date->format(Service::apiDateFormat);
        $date->modify('+1 second');
        $dateAndOneSecondString = $date->format('Ymd\TH:i:s');

        //This is the base method that returns a data array
        $clawbacks = APIAffiliateService::affClawbacks($affiliateId, $dateString, $dateAndOneSecondString, $app);

        $clawbacksInvoice = array(); //commissions with matching invoice Id
        foreach ($clawbacks as $clawback) {
            if ($clawback->InvoiceId == $invoiceId){
                $clawbacksInvoice[] = $clawback;
            }
        }

        if ($index >= 0 && $index < count($clawbacksInvoice) )
            $this->data = $clawbacksInvoice[$index]->toArray();
        else
            throw new Exception("Invalid commission Id");
    }
}
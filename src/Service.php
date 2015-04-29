<?php
namespace NovakSolutions\Infusionsoft;

class Service{
	protected static function _returnResults($className, $appHostName, $records, $returnFields = false){
        if(!$records){
            return array();
        }        
        
        if(!is_array($records)){
			$records = array($records);
		}    	      		
		
        $return_records = array();
                                   
        foreach ($records as $record){
            if ($returnFields != false){
                $record = array_merge(array_fill_keys($returnFields, null), $record);
            }
        	$object = new $className();        	
            $object->loadFromArray($record, true);
            $object->setAppPoolAppKey($appHostName);
            $return_records[] = $object;             
        }        
        
        return $return_records; 	
    }

    protected static function _returnResult($className, $appHostName, $record){
    	$object = new $className();    	        
        $object->loadFromArray($record, true);
        $object->setAppPoolAppKey($appHostName);        
        return $object;        	
    }
    
    protected static function getObjectOrDefaultAppIfNull(App $app = null, Base $object = null){
    	//If an app is explicitly set, skip this...
    	if($app == null){			    		
    		//If the object has an app key, return it's app       				    		
	    	if($object != null && $object->getAppPoolAppKey() != null){			
				$app = AppPool::getApp($object->getAppPoolAppKey());
			}
			//Otherwise, return the default app.
			else{
	    		$app = AppPool::getApp();
			}	
    	}    	
    	return $app;
    }
    
    protected static function send($app, $method, $params, $object = null, $retry = false){
    	$app = self::getObjectOrDefaultAppIfNull($app, $object);
    	return $app->send($method, $params, $retry);
    }

    // Takes a string, parses it and returns a representing the same date/time in XMLRPC's date format
    public static function apiDate($dateStr){
        $dArray = date_parse($dateStr);
        if ($dArray['error_count'] < 1) {
            $tStamp =
                    mktime($dArray['hour'], $dArray['minute'], $dArray['second'], $dArray['month'],
                           $dArray['day'], $dArray['year']);
            return date('Ymd\TH:i:s', $tStamp);
        } else {
            foreach ($dArray['errors'] as $err) {
                echo "ERROR: " . $err . "<br />";
            }
            die("The above errors prevented the application from executing properly.");
        }
    }

    // PHP date representation of the XMLRPC date format, for use with other conversions.

    const apiDateFormat = 'Ymd\TH:i:s';

    public static function getDefaultAppIfNull($app){
        if($app == null){
            return AppPool::getApp();
        }
    }
}
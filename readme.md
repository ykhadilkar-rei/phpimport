Run following command to import JSON datasets
php import.php local test-organization-1

Here is a sample JSON file

[
    {
        "title": "Data Quality On",
        "description": "Version 1.0", 
        "keyword": ["catalog","inventory"],         
        "modified": "2013-05-09",
        "publisher": "US Department of X",
        "contactPoint": "John Doe",
        "mbox": "ykhadilkar@reisys.com",
        "identifier": "1",
        "accessLevel": "public",
        "bureauCode": ["018:10"],
        "programCode": ["018:001"],
        "accessLevelComment":"this is access level comment coming from JSON",
        "license" : "GNU Free Documentation License",
        "landingPage" : "http://www.khadilkar.net",
		    "spatial" : "Lincoln, Nebraska",
		    "theme" : ["vegetables","produce"],
		    "dataDictionary" : "http://www.agency.gov/vegetables/dictionary.html",
		    "dataQuality":"on",
		    "accrualPeriodicity" : "Annual",
		    "landingPage" : "http://www.agency.gov/vegetables",
		    "language" : ["es-MX","wo","nv","en-US"],
		    "PrimaryITInvestmentUII":"023-000000001",
		    "references":["http://www.agency.gov/legumes/legumes_data_documentation.html","http://www.agency.gov/fruits/fruit_data_documentation.html"],
		    "issued":"2001-01-15",
		    "systemOfRecords" : "http://www.agency.gov/vegetables",
        "distribution": [
            {
                "accessURL": "http://khadilkar.net/sites/default/files/country.csv",
                "format": "application/json"
            }
        ]  
    },
    {
        "title": "Data Quality True",
        "description": "Version 1.0", 
        "keyword": ["catalog","inventory"],         
        "modified": "2013-05-09",
        "publisher": "US Department of X",
        "contactPoint": "John Doe",
        "mbox": "ykhadilkar@reisys.com",
        "identifier": "1",
        "accessLevel": "public",
        "bureauCode": ["018:10"],
        "programCode": ["018:001"],
        "accessLevelComment":"this is access level comment coming from JSON",
        "license" : "GNU Free Documentation License",
        "landingPage" : "http://www.khadilkar.net",
    		"spatial" : "Lincoln, Nebraska",
    		"theme" : ["vegetables","produce"],
    		"dataDictionary" : "http://www.agency.gov/vegetables/dictionary.html",
    		"dataQuality":true,
    		"accrualPeriodicity" : "Annual",
    		"landingPage" : "http://www.agency.gov/vegetables",
    		"language" : ["es-MX","wo","nv","en-US"],
    		"PrimaryITInvestmentUII":"023-000000001",
    		"references":["http://www.agency.gov/legumes/legumes_data_documentation.html","http://www.agency.gov/fruits/fruit_data_documentation.html"],
    		"issued":"2001-01-15",
    		"systemOfRecords" : "http://www.agency.gov/vegetables",
        "distribution": [
            {
                "accessURL": "http://khadilkar.net/sites/default/files/country.csv",
                "format": "application/json"
            }
        ]  
    }
]

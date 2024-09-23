<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Import;

class Mapping
{
    public static function reverseMapping($original_keys)
    {
        $reverse_mapping = [];

        // Iterate through each key in the original array
        foreach ($original_keys as $unified_key => $csv_headers) {
            // Iterate through each CSV header for the current unified key
            foreach ($csv_headers as $csv_header) {
                // Map each CSV header to the unified key
                $reverse_mapping[$csv_header] = $unified_key;
            }
        }

        return $reverse_mapping;
    }

    public static function universalMapping()
    {
        // mapped keys showing our key and possible input keys
        return [
            // v2
            'dealer_id' => ['dealer_id', 'DealerID', 'Dealer_ID', 'DEALERID', 'Dealerid', 'Dealer Id'],
            'vin' => ['vin', 'VIN', 'VIN_No', 'Vin'],
            'stock_number' => ['stock_number', 'StockNumber', 'Stock_No', 'STOCK', 'Stock', 'Stockno'],
            'year' => ['year', 'Year', 'YEAR'],
            'make' => ['make', 'Make', 'MAKE'],
            'model' => ['model', 'Model', 'MODEL'],
            'trim' => ['trim', 'Trim', 'TRIM'],
            'body' => ['body', 'Body', 'BodyStyle', 'BODY', 'Body_Style', 'body_style'],
            'mileage' => ['mileage', 'Mileage', 'MILES', 'Miles', 'mileage.value'],
            'transmission' => ['transmission', 'Transmission', 'TRANSMISSION', 'Tramsission', 'TransmissionDescription', 'TransmissionType', 'Transmission_Description'],
            'engine' => ['engine', 'Engine', 'ENGINE', 'EngineDescription'],
            'engine_cylinders' => ['engine_cylinders', 'Cylinders', 'EngineCylinders', 'Engine_Cylinders'],
            'engine_displacement' => ['engine_displacement', 'EngineDisplacement', 'Displacement', 'EngineDisplacementCubicInches'],
            'fuel_type' => ['fuel_type', 'FuelType', 'FUELTYPE', 'Fuel_Type', 'FuelTypeShort'],
            'drive_train' => ['drive_train', 'DriveTrain', 'DRIVETRAIN', 'DriveType', 'drivetrain'],
            'internet_price' => ['internet_price', 'InternetPrice', 'Internet Price', 'Internet_Price'],
            'invoice' => ['invoice', 'Invoice', 'INVOICE'],
            'certified' => ['certified', 'Certified', 'CERTIFIED', 'IsCpo'],
            'doors' => ['doors', 'Doors', 'DOORS'],
            'exterior_color' => ['exterior_color', 'OEMColorNameExterior', 'ExtColor', 'ExteriorColor', 'Ext_Color', 'ExteriorColorGeneric', 'Ext_Color_Generic', 'ExtColorHexCode'],
            'interior_color' => ['interior_color', 'OEMColorNameInterior', 'IntColor', 'InteriorColor', 'Int_Color', 'Int_Color_Generic', 'IntColorHexCode'],
            'photo_urls' => ['photo_urls', 'PhotoUrls', 'ImageURLs', 'ImageList', 'Photo_URLs', 'PhotoURL'],
            'date_in_stock' => ['date_in_stock', 'Date_In_Stock', 'DATE-IN-STOCK', 'DateInStock', 'DATE_IN_STOCK'],
            'date_added' => ['date_added', 'DateAdded', 'DateCreated'],
            'date_modified' => ['date_modified', 'DateModified', 'DateUpdated', 'LastUpdated', 'LastPhotoUpdateUtc', 'ImageModifiedDate', 'PhotosModifiedDate'],
            'price' => ['price', 'Price', 'PRICE', 'sale_price', 'Low_Price', 'High_Price', 'Preferred_Price', 'PrimaryPriceOrMSRP'],
            'sale_price' => ['sale_price', 'SalePrice', 'SellingPrice'],
            'msrp' => ['msrp', 'MSRP', 'Msrp'],
            'lease_payment' => ['lease_payment', 'LeasePayment'],
            'internet_special' => ['internet_special', 'InternetSpecial', 'Internet_Special'],
            'book_value' => ['book_value', 'BookValue', 'BOOKVALUE'],
            'description' => ['description', 'Description', 'DESCRIPTIONS', 'Descriptions', 'Comments', 'DepartmentComments', 'VehicleComments'],
            'options' => ['options', 'Options', 'OPTIONS', 'Features', 'Equipment', 'Categorized Options'],
            'video_url' => ['video_url', 'VideoURL', 'VideoPlayerUrl', 'VideoEmbedUrl'],
            'fuel_economy_city' => ['fuel_economy_city', 'FuelEconomyCity', 'MPGCity', 'MPG_City'],
            'fuel_economy_highway' => ['fuel_economy_highway', 'FuelEconomyHighway', 'MPGHighway', 'MPG_Highway'],
            'vehicle_status' => ['vehicle_status', 'Status', 'VEHICLE STATUS', 'StatusCode', 'VehicleCondition'],
            'dealer_name' => ['dealer_name', 'Company_Name', 'Dealer Name'],
            'dealer_address' => ['dealer_address', 'Company_Address', 'Address', 'Dealer Address'],
            'dealer_city' => ['dealer_city', 'Company_City', 'City', 'Dealer City'],
            'dealer_state' => ['dealer_state', 'Company_State', 'State', 'Dealer State'],
            'dealer_zip' => ['dealer_zip', 'Company_Zip', 'Zip', 'Dealer Zip'],
            'stock_type' => ['stock_type', 'NEW-USED', 'New_Used', 'Type', 'Type New/Used', 'NEW_USED'],
            'vehicle_condition' => ['vehicle_condition', 'VehicleCondition', 'condition'],
            'carfax_one_owner' => ['carfax_one_owner', 'CarfaxOneOwner'],
            'carfax_available' => ['carfax_available', 'CarfaxAvailable', 'Carfax'],
            'certification_warranty' => ['certification_warranty', 'CertificationWarranty'],
            'warranty_month' => ['warranty_month', 'WarrantyMonth'],
            'warranty_miles' => ['warranty_miles', 'WarrantyMiles'],
            'listing_url' => ['listing_url', 'VehicleUrl', 'Listing_URL', 'Url', 'DetailPageUrl', 'VDPURL', 'Website VDP URL'],
        ];
    }

    public function defaults()
    {
        return
            [
                'default' => [
                    'name' => 'Default',
                    'description' => 'Universal template',
                    'template' => self::universalMapping()
                ],
                'b413a8f1821b995d8b43a959d051f3dc' =>
                [
                    'name' => 'VAuto',
                    'description' => 'VAuto Test',
                    'template' => [
                        "address.addr1" => "address_addr1",
                        "address.city" => "address_city",
                        "address.region" => "address_region",
                        "address.country" => "address_country",
                        "body_style" => "body_style",
                        "Dealer ID" => "dealer_id",
                        "Dealer Postal Code" => "dealer_postal_code",
                        "drivetrain" => "drivetrain",
                        "exterior_color" => "exterior_color",
                        "fuel_type" => "fuel_type",
                        "image[0].url" => "image_0_url",
                        "image[0].tag[0]" => "image_0_tag_0",
                        "make" => "make",
                        "mileage.value" => "mileage_value",
                        "mileage.unit" => "mileage_unit",
                        "model" => "model",
                        "price" => "price",
                        "sale_price" => "sale_price",
                        "transmission" => "transmission",
                        "state_of_vehicle" => "state_of_vehicle",
                        "trim" => "trim",
                        "url" => "url",
                        "vin" => "vin",
                        "year" => "year"
                    ]
                ],
            ];
    }

    public function get($template_id)
    {
        return $this->defaults()[$template_id] ?? $this->defaults()['default'];
    }
}

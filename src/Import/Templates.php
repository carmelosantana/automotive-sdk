<?php

declare(strict_types=1);

namespace WpAutos\Vehicles\Import;

class Templates
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

    public static function universalKeyMapping()
    {
        // mapped keys showing our key and possible input keys
        return [
            // v2
            'dealer_id' => ['DealerID', 'Dealer_ID', 'DEALERID', 'Dealerid', 'Dealer Id'],
            'vin' => ['VIN', 'VIN_No', 'Vin'],
            'stock_number' => ['StockNumber', 'Stock_No', 'STOCK', 'Stock', 'Stockno'],
            'year' => ['Year', 'YEAR'],
            'make' => ['Make', 'MAKE'],
            'model' => ['Model', 'MODEL'],
            'trim' => ['Trim', 'TRIM'],
            'body' => ['Body', 'BodyStyle', 'BODY', 'Body_Style', 'body_style'],
            'mileage' => ['Mileage', 'MILES', 'Miles', 'mileage.value'],
            'transmission' => ['Transmission', 'TRANSMISSION', 'Tramsission', 'TransmissionDescription', 'TransmissionType', 'Transmission_Description'],
            'engine' => ['Engine', 'ENGINE', 'EngineDescription'],
            'engine_cylinders' => ['Cylinders', 'EngineCylinders', 'Engine_Cylinders'],
            'engine_displacement' => ['EngineDisplacement', 'Displacement', 'EngineDisplacementCubicInches'],
            'fuel_type' => ['FuelType', 'FUELTYPE', 'Fuel_Type', 'FuelTypeShort'],
            'drive_train' => ['DriveTrain', 'DRIVETRAIN', 'DriveType', 'drivetrain'],
            'internet_price' => ['InternetPrice', 'Internet Price', 'Internet_Price'],
            'invoice' => ['Invoice', 'INVOICE'],
            'certified' => ['Certified', 'CERTIFIED', 'IsCpo'],
            'doors' => ['Doors', 'DOORS'],
            'exterior_color' => ['OEMColorNameExterior', 'ExtColor', 'ExteriorColor', 'Ext_Color', 'ExteriorColorGeneric', 'Ext_Color_Generic', 'ExtColorHexCode'],
            'interior_color' => ['OEMColorNameInterior', 'IntColor', 'InteriorColor', 'Int_Color', 'Int_Color_Generic', 'IntColorHexCode'],
            'photo_urls' => ['PhotoUrls', 'ImageURLs', 'ImageList', 'Photo_URLs', 'PhotoURL'],
            'date_in_stock' => ['Date_In_Stock', 'DATE-IN-STOCK', 'DateInStock', 'DATE_IN_STOCK'],
            'date_added' => ['DateAdded', 'DateCreated'],
            'date_modified' => ['DateModified', 'DateUpdated', 'LastUpdated', 'LastPhotoUpdateUtc', 'ImageModifiedDate', 'PhotosModifiedDate'],
            'price' => ['Price', 'PRICE', 'sale_price', 'Low_Price', 'High_Price', 'Preferred_Price', 'PrimaryPriceOrMSRP'],
            'sale_price' => ['SalePrice', 'SellingPrice'],
            'msrp' => ['MSRP', 'Msrp'],
            'lease_payment' => ['LeasePayment'],
            'internet_special' => ['InternetSpecial', 'Internet_Special'],
            'book_value' => ['BookValue', 'BOOKVALUE'],
            'description' => ['Description', 'DESCRIPTIONS', 'Descriptions', 'Comments', 'DepartmentComments', 'VehicleComments'],
            'options' => ['Options', 'OPTIONS', 'Features', 'Equipment', 'Categorized Options'],
            'video_url' => ['VideoURL', 'VideoPlayerUrl', 'VideoEmbedUrl'],
            'fuel_economy_city' => ['FuelEconomyCity', 'MPGCity', 'MPG_City'],
            'fuel_economy_highway' => ['FuelEconomyHighway', 'MPGHighway', 'MPG_Highway'],
            'vehicle_status' => ['Status', 'VEHICLE STATUS', 'StatusCode', 'VehicleCondition'],
            'dealer_name' => ['Company_Name', 'Dealer Name'],
            'dealer_address' => ['Company_Address', 'Address', 'Dealer Address'],
            'dealer_city' => ['Company_City', 'City', 'Dealer City'],
            'dealer_state' => ['Company_State', 'State', 'Dealer State'],
            'dealer_zip' => ['Company_Zip', 'Zip', 'Dealer Zip'],
            'stock_type' => ['NEW-USED', 'New_Used', 'Type', 'Type New/Used', 'NEW_USED'],
            'vehicle_condition' => ['VehicleCondition', 'condition'],
            'carfax_one_owner' => ['CarfaxOneOwner'],
            'carfax_available' => ['CarfaxAvailable', 'Carfax'],
            'certification_warranty' => ['CertificationWarranty'],
            'warranty_month' => ['WarrantyMonth'],
            'warranty_miles' => ['WarrantyMiles'],
            'listing_url' => ['VehicleUrl', 'Listing_URL', 'Url', 'DetailPageUrl', 'VDPURL', 'Website VDP URL'],
        ];
    }

    public function defaults()
    {
        return
            [
                'default' => [
                    'name' => 'Default',
                    'description' => 'Universal template',
                    'template' => self::universalKeyMapping()
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

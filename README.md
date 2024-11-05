# [Automotive SDK](https://wipyautos.com)

- [Vehicles](#vehicles)
  - [Endpoints](#endpoints)
    - [Get a List of Vehicles](#get-a-list-of-vehicles)
    - [Get a Single Vehicle by ID](#get-a-single-vehicle-by-id)
    - [Create a New Vehicle](#create-a-new-vehicle)
    - [Update an Existing Vehicle](#update-an-existing-vehicle)
    - [Delete a Vehicle](#delete-a-vehicle)
    - [Get all fields and unique values](#get-all-fields-and-unique-values)

This API allows for managing vehicle data, including creating, retrieving, updating, and deleting vehicles. The API mimics the WordPress REST API structure and uses the same authentication and permission system.

## Vehicles

### Endpoints

#### Get a List of Vehicles

**Method:** `GET`  
**Endpoint:** `/wp-json/automotive-sdk/v1/vehicles`  
**Description:** Retrieve a list of all vehicles.

```bash
curl -X GET "https://wp.org/wp-json/automotive-sdk/v1/vehicles"
```

**Response:**

```json
[
    {
        "id": 1,
        "title": "2023 Toyota Camry SE",
        "meta": {
            "vin": "12345678901234567",
            "price": "25000"
        },
        "taxonomies": {
            "make": "Toyota",
            "model": "Camry",
            "year": "2023"
        }
    }
]
```

#### Get a Single Vehicle by ID

**Method:** `GET`  
**Endpoint:** `/wp-json/automotive-sdk/v1/vehicles/{id}`  
**Description:** Retrieve details of a single vehicle by its ID.

```bash
curl -X GET "https://wp.org/wp-json/automotive-sdk/v1/vehicles/1"
```

**Response:**

```json
{
    "id": 1,
    "title": "2023 Toyota Camry SE",
    "meta": {
        "vin": "12345678901234567",
        "price": "25000"
    },
    "taxonomies": {
        "make": "Toyota",
        "model": "Camry",
        "year": "2023"
    }
}
```

#### Create a New Vehicle

**Method:** `POST`  
**Endpoint:** `/wp-json/automotive-sdk/v1/vehicles`  
**Description:** Create a new vehicle.

```bash
curl -X POST "https://wp.org/wp-json/automotive-sdk/v1/vehicles" \
  -H "Content-Type: application/json" \
  -d '{
        "title": "2023 Honda Civic LX",
        "vin": "0987654321abc1234",
        "price": "22000"
        "make": "Honda",
        "model": "Civic",
        "year": "2023"
    }'
```

**Response:**

```json
{
    "id": 2,
    "title": "2023 Honda Civic LX",
    "meta": {
        "vin": "0987654321abc1234",
        "price": "22000"
    },
    "taxonomies": {
        "make": "Honda",
        "model": "Civic",
        "year": "2023"
    }
}
```

#### Update an Existing Vehicle

**Method:** `POST`  
**Endpoint:** `/wp-json/automotive-sdk/v1/vehicles/{id}`  
**Description:** Update the details of an existing vehicle by its ID.

```bash
curl -X POST "https://wp.org/wp-json/automotive-sdk/v1/vehicles/2" \
  -H "Content-Type: application/json" \
  -d '{
        "title": "2023 Honda Civic EX",
        "price": "23000"
    }'
```

**Response:**

```json
{
    "id": 2,
    "title": "2023 Honda Civic EX",
    "meta": {
        "vin": "0987654321abc1234",
        "price": "23000"
    },
    "taxonomies": {
        "make": "Honda",
        "model": "Civic",
        "year": "2023"
    }
}
```

#### Delete a Vehicle

**Method:** `DELETE`  
**Endpoint:** `/wp-json/automotive-sdk/v1/vehicles/{id}`  
**Description:** Delete a vehicle by its ID.

```bash
curl -X DELETE "https://wp.org/wp-json/automotive-sdk/v1/vehicles/2"
```

**Response:**

```json
{
    "message": "Vehicle deleted successfully."
}
```

#### Get all fields and unique values

**Method:** `GET`  
**Endpoint:** `/wp-json/automotive-sdk/v1/vehicles/fields`  
**Description:** Retrieve a list of all possible vehicle fields and their unique values.

```bash
curl -X GET "https://wp.org/wp-json/automotive-sdk/v1/vehicles/fields"
```

**Response:**

```json
{
    "vin": {
      "type": "text",
      "values": [
        "2FMZA51665BA05533",
        "2S3DA417576128786",
        "JS2RA41S235163290"
      ]
    },
    "stock_number": {
      "type": "text",
      "values": [
        "16723PX",
        "16767PT",
        "16774PX"
      ]
    },
    "transmission": {
      "type": "text",
      "values": [
        "6-Speed Automatic w/OD",
        "CVT w/OD",
        "9-Speed Automatic w/OD"
      ]
    },
    "fuel_type": {
      "type": "text",
      "values": ["Gasoline Fuel", "Hybrid Fuel"]
    },
    "drive_train": {
      "type": "text",
      "values": ["AWD", "FWD", "RWD"]
    },
    "internet_price": {
        "type": "number",
        "min": "7999",
        "max": "45999"
    },
    "year": {
      "type": "string",
      "values": ["2022", "2023", "2024"]
    },
    "make": {
      "type": "string",
      "values": ["Honda", "Toyota", "Subaru"]
    },
    "model": {
      "type": "string",
      "values": ["Accord", "Camry", "Outback"]
    },
    "trim": {
      "type": "string",
      "values": ["EX", "SE", "Touring"]
    },
    "body": {
      "type": "string",
      "values": ["Sedan", "SUV", "Hatchback"]
    }
}
```

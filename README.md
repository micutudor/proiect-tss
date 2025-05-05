# Vehicle Management API Tests

## Features

- Create new vehicle records
- Read all vehicles or a specific one by ID
- Update existing vehicle details
- Delete vehicles
- Input validation (license plate format)
- JSON API response format

---

## CRUD API Endpoints

| Method | Endpoint               | Description                      |
|--------|------------------------|----------------------------------|
| GET    | `/vehicles`            | List all vehicles                |
| GET    | `/vehicles/{id}`       | Retrieve a specific vehicle      |
| POST   | `/vehicles`            | Create a new vehicle             |
| PUT    | `/vehicles/{id}`       | Update an existing vehicle       |
| DELETE | `/vehicles/{id}`       | Delete a vehicle                 |

---

## Unit Tests

All tests are written using **PHPUnit** and are located in the `tests/` directory.

### Test 1: `testGetAllReturnsVehicles`

**Purpose:**  
Tests if `VehicleController::getAll()` returns all vehicles in proper JSON format with status 200.

**Covers:**
- Controller logic for `GET /vehicles`
- PSR-7 response structure
- Dependency mocking (Vehicle model)

**100% PASS WHEN:**
- `Vehicle::all()` returns an array
- JSON is correctly written to the response body
- Status code is 200
- Response contains expected vehicle data

---

### Test 2: `testCreateReturnsCreated`

**Purpose:**  
Tests if `VehicleController::create()` validates input and creates a vehicle successfully.

**Covers:**
- Controller logic for `POST /vehicles`
- Input format validation for license plate
- PSR-7 response code 201
- Mocked call to `Vehicle::save()`

**100% PASS WHEN:**
- Valid license plate is passed (e.g., `B123XYZ`)
- Controller invokes `save()` once with correct data
- Returns response with status 201 and proper headers

---

### Test 3: `testUpdateReturnsSuccess`

**Purpose:**  
Tests if `VehicleController::update()` updates a vehicle correctly.

**Covers:**
- Controller logic for `PUT /vehicles/{id}`
- Lookup of existing vehicle
- Input validation for license plate
- Use of `update()` method on the model

**100% PASS WHEN:**
- `Vehicle::find()` returns a valid record
- Valid update data is passed
- `update()` is called once with correct parameters
- Response has status 200

---

## Types of Tests

- **Unit Tests**: Each method is tested in isolation by mocking dependencies (no database connection).
- **Behavioral Coverage**:
  - Response status codes
  - Output content (JSON)
  - Input validation
  - PSR-7 compliance

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

## Functional Testing (Black-Box Testing)

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

## Equivalence Classes

**What is Equivalence Partitioning**

- Equivalence partitioning (or equivalence classes) is a test design technique where the input data of a program is divided into partitions of valid and invalid data. Each partition represents a set of values that the system is expected to handle in the same way.
- Instead of testing every possible input, it is enough to test each partition — assuming it represents the rest.

**Equivalence Classes in VehicleController**

1. create(Request $request, Response $response)
   
| Input                          | Class Type | Description                                       | Example                   |
| ------------------------------ | ---------- | ------------------------------------------------- | ------------------------- |
| `$data['numar_inmatriculare']` | Valid      | String matching `/^[A-Z]{1,2}\d{2,3}[A-Z]{1,3}$/` | `B123ABC`                 |
|                                | Invalid    | Any string not matching the pattern               | `1234BCA`, `abc123`, `""` |

2. update(Request $request, Response $response, $args)

| Input                          | Class Type | Description               | Example           |
| ------------------------------ | ---------- | ------------------------- | ----------------- |
| `$args['id']`                  | Valid      | Existing vehicle ID       | `5`               |
|                                | Invalid    | Non-existing vehicle ID   | `999`             |
| `$data['numar_inmatriculare']` | Valid      | String matching regex     | `CJ123XYZ`        |
|                                | Invalid    | String not matching regex | `AB-123-CD`, `""` |

3. getById(Request $request, Response $response, $args)
   
| Input         | Class Type | Description             | Example |
| ------------- | ---------- | ----------------------- | ------- |
| `$args['id']` | Valid      | Existing vehicle ID     | `3`     |
|               | Invalid    | Non-existing vehicle ID | `404`   |

4. delete(Request $request, Response $response, $args)

| Input         | Class Type | Description             | Example |
| ------------- | ---------- | ----------------------- | ------- |
| `$args['id']` | Valid      | Existing vehicle ID     | `7`     |
|               | Invalid    | Non-existing vehicle ID | `404`   |

## Boundary Value Tests

Boundary value tests are a type of black-box testing technique in software testing, focused on identifying errors at the boundaries of input domains rather than within the range.

### 1. create(Request $request, Response $response)

**Input**: $data['numar_inmatriculare]  
**Pattern**: /^[A-Z]{1,2}\d{2,3}[A-Z]{1,3}$/

**Boundaries**:
- Number of letters at the start: 1 or 2 uppercase letters
- Number of digits: 2 or 3 digits
- Number of letters at the end: 1 to 3 uppercase letters

**Test Cases**:

| Test Case               | Input    | Expected Result |
| ----------------------- | -------- | --------------- |
| Minimum valid           | B12A     | 201 Created     |
| Maximum valid           | AB123ABC | 201 Created     |
| Below minimum digits    | B1A      | 400 Bad Request |
| Above maximum digits    | B1234A   | 400 Bad Request |
| Below min letters start | 1B23C    | 400 Bad Request |
| Above max letters start | ABC123A  | 400 Bad Request |
| Below min letters end   | B123     | 400 Bad Request |
| Above max letters end   | B123ABCD | 400 Bad Request |

---

### 2. update(Request $request, Response $response, $args)

**Input**:
- $args['id'] (as primary key/identifier)
- $data['numar_inmatriculare'] (same pattern as above)

**Boundaries**:
- **ID**:
   - Minimum valid ID: 1
   - Non-existing ID: assuming max existing is 100, test with 101

**Test Cases**:

| Test Case              | Input ID | numar_inmatriculare | Expected Result |
| ---------------------- | -------- | -------------------- | --------------- |
| Valid min ID           | 1        | B12A                 | 200 OK          |
| Valid max ID           | 100      | AB123ABC             | 200 OK          |
| Non-existing below min | 0        | any                  | 404 Not Found   |
| Non-existing above max | 101      | any                  | 404 Not Found   |
| Valid min pattern      | 1        | B12A                 | 200 OK          |
| Valid max pattern      | 1        | AB123ABC             | 200 OK          |
| Invalid pattern        | 1        | 1234A                | 400 Bad Request |

---

### 3. getById(Request $request, Response $response, $args)

**Boundaries**:
- **ID value:**
   - Existing IDs: 1 to 100
   - Non-existing: 0 or 101

**Test Cases**:

| Test Case       | Input ID | Expected Result |
| --------------- | -------- | --------------- |
| Existing min ID | 1        | 200 OK          |
| Existing max ID | 100      | 200 OK          |
| Below min       | 0        | 404 Not Found   |
| Above max       | 101      | 404 Not Found   |

---

### 4. delete(Request $request, Response $response, $args)

**Boundaries**:
- **ID value:**
   - Existing IDs: 1 to 100
   - Non-existing: 0 or 101

**Test Cases**:

| Test Case       | Input ID | Expected Result |
| --------------- | -------- | --------------- |
| Existing min ID | 1        | 204 No Content  |
| Existing max ID | 100      | 204 No Content  |
| Below min       | 0        | 404 Not Found   |
| Above max       | 101      | 404 Not Found   |

---

## Structural Testing (White-Box Testing)

### Control Flow Graph (CFG)

![CFG](https://github.com/user-attachments/assets/f4716743-8f78-4b0f-9a51-a549708f653e)

### Flow Steps

| Label                     | Step Description         | Purpose                                                    |
| ------------------------- | ------------------------ | ---------------------------------------------------------- |
| *A*                     | *Start Request*        | Entry-point for every call                                 |
| *B*                     | *Identify HTTP Method* | Dispatches to the correct handler (GET, POST, PUT, DELETE) |
| *GET /vehicles/{id}*    |                          |                                                            |
| C1                        | getById                | Controller entry                                           |
| C2                        | Find vehicle by ID       | Repository lookup                                          |
| C3                        | 404 Not Found            | Vehicle missing                                            |
| C4                        | 200 OK + data            | Vehicle found                                              |
| *POST /vehicles*        |                          |                                                            |
| D1                        | create                 | Controller entry                                           |
| D2                        | Validate licence plate   | Business rule check                                        |
| D3                        | 400 Bad Request          | Plate invalid                                              |
| D4                        | Save vehicle             | Persist entity                                             |
| D5                        | 500 Error                | DB failure                                                 |
| D6                        | 201 Created              | Persist succeeded                                          |
| *PUT /vehicles/{id}*    |                          |                                                            |
| E1                        | update                 | Controller entry                                           |
| E2                        | Find vehicle by ID       | Repository lookup                                          |
| E3                        | 404 Not Found            | Vehicle missing                                            |
| E4                        | Validate licence plate   | Business rule check                                        |
| E5                        | 400 Bad Request          | Plate invalid                                              |
| E6                        | Update vehicle           | Persist changes                                            |
| E7                        | 500 Error                | DB failure                                                 |
| E8                        | 200 OK                   | Update succeeded                                           |
| *DELETE /vehicles/{id}* |                          |                                                            |
| F1                        | delete                 | Controller entry                                           |
| F2                        | Find vehicle by ID       | Repository lookup                                          |
| F3                        | 404 Not Found            | Vehicle missing                                            |
| F4                        | Delete vehicle           | Remove entity                                              |
| F5                        | 500 Error                | DB failure                                                 |
| F6                        | 204 No Content           | Deletion succeeded                                         |
| *Z*                     | *End*                  | Unified exit node                                          |

---

### Cyclomatic Complexity

Using McCabe’s formula
  *M = E – N + 2 P*

* *N* (nodes) = 27
* *E* (edges) = 37
* *P* (connected components) = 1

*M = 37 – 27 + 2 × 1 = 12*

So the control-flow graph has a cyclomatic complexity of *12*, meaning there are 12 linearly independent execution paths through the API request logic.

## Types of Tests

- **Unit Tests**: Each method is tested in isolation by mocking dependencies (no database connection).
- **Behavioral Coverage**:
 - Response status codes
 - Output content (JSON)
 - Input validation
 - PSR-7 compliance
 - **Equivalence Partitioning**:
    - Inputs are divided into **valid** and **invalid** equivalence classes
    - Allows for **representative testing** without checking every possible input
    - Helps verify how the controller handles **boundary** and **non-conforming** data
- **Boundary Value Tests:**
   - Pattern Validation for License Plate for the valid license plate format using the regular expression /^[A-Z]{1,2}\d{2,3}[A-Z]{1,3}$/, which ensures that inputs are tested against the minimum and maximum number of characters allowed in each section (start letters, digits, and end letters).
   - For methods like update, getById, and delete, using ID boundaries (min ID = 1, max ID = 100), as well as non-existing IDs (e.g., 0 and 101), simulate edge cases of accessing valid vs invalid records. This ensures that the system correctly handles valid IDs (responses 200 OK or 204 No Content) and returns 404 Not Found for invalid IDs.
 - **Structural Testing**
   - A detailed **Control Flow Graph (CFG)** was created for all controller logic (create, getById, update, delete).
   - **Cyclomatic Complexity = 12**, indicating 12 independent execution paths through the controller.
   - Ensures:
     - All branches, conditions, and response codes are exercised
     - Test coverage across normal, error, and edge paths


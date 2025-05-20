# AI Comparison Report – Completed (English)
## 1. Context
The code we tested: Vehicle.php (model) and VehicleController.php (controller).

Original prompt: “For the following model class (Vehicle.php) and controller (VehicleController.php) write me structural and functional tests, covering all the HTTP methods. ”

## 2. Summary of the AI-generated tests
Structural (VehicleAIStructuralTest):
- It checks if every required public method exists.
- Implies the correct parameter count and also declared return types.
- It doesn't execute business logic.
  
Functional (VehicleAIFunctionalTest):
- Builds the schema and wraps every example in a DB trasaction.
- Verifies full CRUD flow and also 2 negative scenarios.

## 3. Summary of your current test suites
| Suite                                  | Testing technique                    | Strengths                                                                                                             |
| -------------------------------------- | ------------------------------------ | --------------------------------------------------------------------------------------------------------------------- |
| **VehicleBoundaryTest**                | *Boundary value analysis*            | Tests create/get/delete at the minimum & maximum acceptable values (plates and IDs) using PHPUnit mocks—no DB needed. |
| **VehicleConditionalCoverageTest**     | *Condition/decision coverage*        | Explicitly drives every boolean branch (`!$vehicle`, `!preg_match` etc.).                                             |
| **VehicleDecisionCoverageTest**       | *Branch/decision coverage*           | Confirms each major branch in every controller method (all mocked).
| **VehicleEquivalencePartitioningTest** | *Equivalence partitioning*           | Splits inputs into valid/invalid classes and checks each.                                                             |
| **VehicleIndependentCircuitsTest**     | *Path / cyclomatic-circuit coverage* | Executes distinct control-flow paths for maximum coverage.                                                            |

Common traits of your suites
- All rely on PHPUnit mocks, no external dependencies.
- Tests are grouped by testing technique.

## 4. Key differences
| Aspect                       | AI tests                                 | Our tests                                                        |
| ---------------------------- | ---------------------------------------- | ----------------------------------------------------------------- |
| **Isolation level**          | True integration (real DB).              | Pure unit (mocks only).                                           |
| **Granularity**              | 2 files: 1 structural, 1 functional.     | 5 focused files, each targeting a specific test design technique. |
| **Infrastructure needs**     | Creates/drops tables, uses transactions. | None—runs anywhere.                                               |
| **CRUD coverage**            | Full flow in one test.                   | CRUD covered but broken into multiple, more granular tests.       |
| **CI performance**           | Slower due to DB hits.                   | Very fast                                |

## 5. Conclusion
AI output supplies a minimal but valuable contract test and a true integration test.

Our suites deliver thorough unit-level coverage across multiple classic testing strategies.


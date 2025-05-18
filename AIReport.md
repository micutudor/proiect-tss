# AI Comparison Report

## 1 · High-level snapshot

| Metric                            | **Our test-suite** | **Suite AI provided**                                    |
| --------------------------------- | ------------------- | ------------------------------------------------------- |
| Number of test classes            | **5**               | **2**                                                   |
| Number of individual test methods | **44**              | **9**                                                   |
| Total assertions                  | **46**              | **≈ 45**                                                |

---

## 2 · Scope & focus

| Area / Concern                      | **Our tests** | **AI provided tests**                                                                  |
| ----------------------------------- | -------------- | ----------------------------------------------------------------------------------- |
| **Structural/Contract checks**      | `___________`  | ✔ Reflective tests validate public API (methods, visibility, params, return types). |
| **Functional (HTTP-level) flow**    | `___________`  | ✔ End-to-end CRUD via controller with PSR-7 objects.                                |
| **Validation logic** (plate format) | `___________`  | ✔ Positive + negative cases for regex.                                              |
| **Error paths (404/400)**           | `___________`  | ✔ Missing ID & invalid plate.                                                       |
| **Happy-path CRUD**                 | `___________`  | ✔ Create → List → Update → Show → Delete cycle.                                     |
| **Database isolation**              | `___________`  | Transaction per test, rolled back in `tearDown()`.                                  |
| **Schema bootstrap**                | `___________`  | Auto-creates table if missing (MySQL).                                              |
| **Performance optimisation**        | `___________`  | Uses single PDO + stubs wrapper; no external HTTP server.                           |
| **Edge-case dates (NULL columns)**  | `___________`  | Only partially (can be extended).                                                   |

---

## 3 · Database Strategy

| Aspect               | **Our suite** | **AI provided suite**                                  |
| -------------------- | -------------- | --------------------------------------------------- |
| RDBMS used           | `___________`  | MySQL (configurable via `TEST_DB_*` env vars).      |
| Setup                | `___________`  | `CREATE TABLE IF NOT EXISTS …` once per run.        |
| Clean-up             | `___________`  | `DELETE FROM autovehicule` + transaction rollback.  |
| Test isolation level | `___________`  | Each test fully isolated; no residue between cases. |

---


## 4 · Strengths & gaps

| Dimension        | **Our suite – strengths** | **Our suite – gaps / TODO** | **Ai provided suite – strengths**                | **Ai provided suite – gaps / TODO**         |
| ---------------- | -------------------------- | ---------------------------- | --------------------------------------------- | ---------------------------------------- |
| Readability      | `___________`              | `___________`                | High (descriptive names, inline docs)         | Could split long CRUD test for clarity.  |
| Extensibility    | `___________`              | `___________`                | Easy to extend (helper methods, env config)   | Needs mutation-testing / faker data.     |
| Coverage breadth | `___________`              | `___________`                | Covers core flows & contracts                 | Missing date-edge-cases + SQL error path |
| Runtime speed    | `___________`              | `___________`                | Fast (single connection, transactions)        | Slight MySQL overhead vs SQLite.         |
| CI friendliness  | `___________`              | `___________`                | No global state, works in parallel containers | Requires MySQL service in CI.            |

---


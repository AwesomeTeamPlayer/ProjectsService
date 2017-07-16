# ProjectService microservice

## Endpoints

**PUT /users** 
```json
{
    "userId": 123,
    "projectId": "abc123"
}
```
If user was successfully added to project returns `{"status":"ok"}`.

It creates event:
```json
{
  "name": "AddedUserToProject",
  "occuredAt": "2017-07-16T11:02:05+02:00",
  "data": {
    "userId": 123,
    "projectId": 456
  }
}
```


If user had access to a specified project before, it returns `{"status":"ok"}`.


**DELETE /users** 
```json
{
    "userId": 123,
    "projectId": "abc123"
}
```
If user was successfully removed from project returns `{"status":"ok"}`.


It creates event:
```json
{
  "name": "RemovedUserFromProject",
  "occuredAt": "2017-07-16T11:02:05+02:00",
  "data": {
    "userId": 123,
    "projectId": 456
  }
}
```

If user had not access to a specified project before, it returns `{"status":"ok"}`.

**GET /users/?project_id={PROJECT_ID}**

It returns list of users' ID's with access to this project, for example:
```json
[
  123, 34, 657
]
```

If project does not exist it returns empty list.


**GET /users/hasAccess?user_id={USER_ID}&project_id={PROJECT_ID}**

It returns information about user's access to specified project:
```json
{
  "hasAccess": true
}
```
If user of project does not exist it returns:
```json
{
  "hasAccess": false
}
```

---

**GET /projects/?user_id={USER_ID}**

It returns list of projects' ID's, for example:
```json
[
  "abc123", "def456", "ghi789"
]
```

If project does not exist it returns empty list.

## Variables:

## How to run unit tests?
```bash
/app/runTests.sh
```

## TODO:
1. Write the code :p


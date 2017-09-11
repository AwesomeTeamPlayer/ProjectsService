# ProjectService microservice

Basic definitions:
* project ID is a string hash. It has 10 characters.
* user ID is a string hash. It has 10 characters.
* all "occurredAt" and "createdAt" fields contains date in ISO 8601 format.

In examples below user's and project's IDs have less characters to keep those examples clear.

## CreateProject
Parameters:
* name (string)
* type (int)
* userIds (array of string)

Example: 
```json
{
    "name":"New project name",
    "type": 123,
    "userIds": ["user_1", "user_2","user_3"]
}
```

It returns string with project ID.

It creates events:
```json
{
  "name": "project.created",
  "occurredAt": "...",
  "data": {
    "projectId": "projectId",
    "name":"New project name",
    "type": 123,
    "createdAt": "2012-12-12T13:22:35+04:00"
  }
}
```
and for each user:
```json
{
  "name": "project.user.added",
  "occurredAt": "...",
  "data": {
    "projectId": "projectId",
    "userId": "user_1"
  }
}
```

## Update project data
Parameters:
* projectId (string) 
* name (string) [optional]
* type (int) [optional]
* usersIds (array or string) [optional] - only this users will have access 
to that project. Other users will be removed
```json
{
    "projectId": "projectId",
    "name":"Updated project name",
    "type": 456,
    "userIds": ["user_5", "user_6","user_7"]
}
```

It return *true*

It create events:
```json
{
  "name": "project.updated",
  "occurredAt": "...",
  "data": {
    "projectId": "projectId",
    "name":"Updated project name",
    "type": 456
  }
}
```

It creates events per each user
```json
{
  "name": "project.user.added",
  "occurredAt": "...",
  "data": {
    "projectId": "projectId",
    "userId": "user_5"
  }
}
```
or/and
```json
{
  "name": "project.user.removed",
  "occurredAt": "...",
  "data": {
    "projectId": "projectId",
    "userId": "user_5"
  }
}
```


## Add users to a project
Parameters:
* projectId (string) 
* userIds (array of strings)

Example: 
```json
{
    "projectId": "projectId",
    "userIds": ["user_1", "user_2","user_3"]
}
```

It returns *true*.

It creates events per each user
```json
{
  "name": "project.user.added",
  "occurredAt": "...",
  "data": {
    "projectId": "h1dhe2da7",
    "userId": "user_1"
  }
}
```


## Remove users from a project
Parameters:
* projectId (string) 
* userIds (array of strings)

Example: 
```json
{
    "projectId": "h1dhe2da7",
    "userIds": ["user_1", "user_2","user_3"]
}
```

It returns *true*.

It creates events per each user
```json
{
  "name": "project.user.removed",
  "occurredAt": "...",
  "data": {
    "projectId": "h1dhe2da7",
    "userId": "user_1"
  }
}
```

## List all projects
Parameters:
* userId (string)
* page (positive integer)
* offset (positive integer)
* filter (one of those values: "all", "archived", "unarchived")
* orderBy (one of those values: "name", "createdAt", "type")
* order (one of those values: "desc", "asc")

Example: 
```json
{
  "userId": "user_1",
  "page": 1,
  "offset": 20,
  "filter": "unarchived",
  "orderBy": "name",
  "order": "desc"
}
```

It returns
```json
{
  "list": [
     {
       "projectId": "projectId_1",
       "name": "New project name",
       "type": 123,
       "isArchived": false,
       "createdAt": "...",
       "userIds": [ "user_1", "user_2", "user_3"]
     },
     // ...
  ],
  "countTotal": 24  
}
```


## Get information about specific project
Parameters:
* projectId (string)

Example: 
```json
{
  "projectId": "projectId_1"
}
```

It returns if project exists
```json
{
   "projectId": "projectId_1",
   "name": "New project name",
   "type": 123,
   "isArchived": false,
   "createdAt": "...",
   "userIds": [ "user_1", "user_2", "user_3"]
}
```

or *false* otherwise.



## Archived specific project
Parameters:
* projectId (string)

Example: 
```json
{
  "projectId": "projectId_1"
}
```

It returns *true* if project exists and can be archived (it exists 
was not archived before) or *false* otherwise.



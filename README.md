# ProjectService microservice

Basic definitions:
* project ID is a string hash. It has 10 characters.
* user ID is a string hash. It has 10 characters.
* all "occurredAt" and "createdAt" fields contains date in ISO 8601 format.

In examples below user's and project's IDs have less characters to keep those examples clear.

## Configuration
This service needs configured below variables:
* MYSQL_HOST
* MYSQL_PORT
* MYSQL_USER
* MYSQL_PASSWORD
* MYSQL_DATABASE
* RABBITMQ_HOST
* RABBITMQ_PORT
* RABBITMQ_USER
* RABBITMQ_PASSWORD
* RABBITMQ_EXCHANGE_NAME

## createProject 
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

It creates events *project.created*:
```json
{
    "projectId": "projectId"
}
```
and for each user *project.user.added*:
```json
{
    "projectId": "projectId",
    "userId": "user_1"
}
```

## updateProject
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

It create *project.name.updated* event if name was changed:
```json
{
    "projectId": "projectId"
}
```

It create *project.type.updated* event if type was changed:
```json
{
    "projectId": "projectId"
}
```

It creates events *project.user.added* per each added user
```json
{
    "projectId": "projectId",
    "userId": "user_5"
}
```
or/and *project.user.removed*
```json
{
    "projectId": "projectId",
    "userId": "user_5"
}
```


## addUsersToProject
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

It creates *project.user.added* events per each user
```json
{
    "projectId": "projectId",
    "userId": "user_5"
}
```


## removeUsersFromProject
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

It creates *project.user.removed* events per each user
```json
{
    "projectId": "projectId",
    "userId": "user_5"
}
```

## listProjects
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
  "page": 0,
  "limit": 20,
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


## getProject
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



## archiveProject
Parameters:
* projectId (string)

Example: 
```json
{
  "projectId": "projectId_1"
}
```

It returns *true* if project exists and can be archived (it was not archived before) 
or *false* otherwise.

It creates *project.archived* event
```json
{
    "projectId": "h1dhe2da7"
}
```


## unarchiveProject
Parameters:
* projectId (string)

Example: 
```json
{
  "projectId": "projectId_1"
}
```

It returns *true* if project exists and can be unarchived (it was archived before)
or *false* otherwise.

It creates *project.unarchived* event
```json
{
  "projectId": "projectId_1"
}
```

# Laravel Medialibrary GraphQL

This package contains GraphQL queries and mutations to manage any type of media files and make them attacheable to any given model.

## About 

We use [Lighthouse](https://lighthouse-php.com/master/getting-started/installation.html) for GraphQL.

The management of the mediafiles is based on the [spatie/laravel-medialibrary](https://docs.spatie.be/laravel-medialibrary/v8/introduction/) package.

By default this package uses Model from config `auth.providers.users.model` for assign files.  
But you can change this after publish package config and change `'laravel-medialibrary-graphql.models.main'` value.  

## Installation

Require the package through composer.

```shell script
compsoer require marqant-lab/laravel-medialibrary-graphql
```

Publish the configuration.

```shell script
php artisan vendor:publish --provider="Marqant\LaravelMediaLibraryGraphQL\Providers\LaravelMediaLibraryGraphQLServiceProvider" --tag=config
```

You will have to run the migrations to setup the media table.

```schell script
php artisan migrate
```

In this config you can specify a model to assign files to ('models.main') and many other settings. The model should implements `Spatie\MediaLibrary\HasMedia` interface and use `Spatie\MediaLibrary\InteractsWithMedia` trait.

For example User model:

```php
<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends ... implements HasMedia
{
    use InteractsWithMedia;
    
    // ...
```

If you need Spatie\MediaLibrary config:

```shell script
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="config"
```

If you plan to use the web route _'media/download/'_ to download files, add the `MEDIA_API_KEY` variable to your `.env` file to secure your applications downloads with an api key.  

You need to set this key as 'apiKey' at headers.

```
GET http://your.awesome.site/media/download/4bb0e054-e98f-4906-b3f5-0277fd63a194/  
Content-Type: application/json  
apiKey: {your_secure_api_key}  
```

This package uses  `@guard`  directive for secure. You need to setup our [marqant-lab/auth-graphql](https://github.com/marqant-lab/auth-graphql) package for this.

And add this to your 'config/lighthouse.php':

After this add import to your `schema.graphql`

```graphql
#import ../vendor/marqant-lab/lighthouse-json/graphql/*.graphql
#import ../vendor/marqant-lab/laravel-medialibrary-graphql/graphql/*.graphql
```

## Queries

| Query         | Requires input                                           | Returns |
| ------------- | -------------------------------------------------------- | ------- |
| getMedia      | id: Int! (ID of the model need to delete all files from) | [Media] |
| downloadMedia | uuid: String!                                            | String! |


## Mutations

| Mutation       | Requires input                                           | Returns |
| -------------- | -------------------------------------------------------- | ------- |
| uploadFile     | id: Int! (ID of the model need to attach file to),       | [Media] |
|                | file: Upload!, name: String, properties: Json            |         |
| deleteMedia    | uuid: String!                                            | String  |
| deleteAllMedia | id: Int! (ID of the model need to delete all files from) | String  |


uploadFile mutation example:

```graphql
mutation UploadFile($id: Int!, $file: Upload!, $name: String, $properties: Json) {
  uploadFile(id: $id, file: $file, name: $name, properties: $properties) {
    id
    name
    fileName
    path
    url
    downloadUrl
    properties
    type
    uuid
    createdAt
    updatedAt
  }
}
```
```json
{
    "id": 1,
    "name": "PDF file",
    "properties": {
        "title": "test title",
        "description": "test description"
    }
}
```
 plus 'file' type Upload (models)  

response example:
```json
{
  "data": {
    "uploadFile": [
      {
        "name": "PDF file",
        "fileName": "001.pdf",
        "path": "",
        "url": "",
        "downloadUrl": "http://your.awesome.site/media/download/4bb0e054-e98f-4906-b3f5-0277fd63a194/",
        "properties": "{\"title\":\"test title\",\"description\":\"test description\"}",
        "type": "pdf",
        "uuid": "4bb0e054-e98f-4906-b3f5-0277fd63a194"
      },
      ...
```

## Tests

If you want to execute package tests add this to the phpunit.xml

```xml
<testsuite name="LaravelMedialibraryGraphQL">
    <directory suffix="Test.php">./vendor/marqant-lab/laravel-medialibrary-graphql/tests</directory>
</testsuite>
```

And after you can check it by executing:

```shell script
php artisan test --group=GraphQLMediaLibrary
# or
phpunit --group=GraphQLMediaLibrary
```

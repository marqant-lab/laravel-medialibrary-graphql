<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Resources;

use Illuminate\Support\Facades\URL;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Media
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Resources
 */
class MediaResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'fileName'    => $this->file_name,
            'path'        => (config('laravel-medialibrary-graphql.properties_flags.enable_media_path')) ? $this->getPath() : '',
            'url'         => (config('laravel-medialibrary-graphql.properties_flags.enable_media_url')) ? $this->getUrl() : '',
            'properties'  => $this->custom_properties,
            'type'        => $this->type,
            'uuid'        => $this->uuid,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'downloadUrl' => (config('laravel-medialibrary-graphql.properties_flags.enable_media_download_url'))
                ? $this->getDownloadUrl($this->uuid) : '',
        ];
    }

    /**
     *
     * @return string
     */
    private function getDownloadUrl($uuid): string
    {
        return URL::to('/') . "/media/download/$uuid/";
    }
}

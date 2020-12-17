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
    private const CONFIG_KEY_PROP = 'laravel-medialibrary-graphql.properties_flags.';
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $additional_field = config('laravel-medialibrary-graphql.additional_field.name') ?? 'additional_field';

        return [
            'id'           => $this->id,
            'uuid'         => $this->uuid,
            'name'         => $this->name,
            'file_name'    => $this->file_name,
            'path'         => (config(self::CONFIG_KEY_PROP . 'enable_media_path')) ? $this->getPath() : '',
            'url'          => (config(self::CONFIG_KEY_PROP . 'enable_media_url')) ? $this->getUrl() : '',
            'properties'   => $this->custom_properties,
            'type'         => $this->type,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'download_url' => (config(self::CONFIG_KEY_PROP . 'enable_media_download_url'))
                ? $this->getDownloadUrl($this->uuid) : '',
            $this->toCamelCase($additional_field) => $this->{$additional_field},
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

    /**
     * Converts snake_case to camelCase
     *
     * @param string $word
     *
     * @return string
     */
    private function toCamelCase(string $word): string
    {
        return lcfirst(str_replace(' ', '', ucwords(strtr($word, '_-', ' '))));
    }
}

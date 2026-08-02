<?php

namespace App\Services\UserManagementService\Infrastructure\Mappers;

use App\Services\UserManagementService\Domain\Entities\OAuthProvider as DomainOAuthProvider;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\SocialProvider as EloquentOAuthProvider;
use Zolta\Cqrs\Repositories\Mapper\RepositoryMapper;
use Zolta\Domain\ValueObjects\OAuthProvider as OAuthProviderEnum;
use Zolta\Domain\ValueObjects\OAuthProviderId;

class SocialProviderMapper implements RepositoryMapper
{
    /**
     * Map a domain entity to a persistence model or array.
     */
    public static function toPersistence(object $entity): object|array
    {
        if ($entity instanceof DomainOAuthProvider) {
            return self::toNewEloquent($entity);
        }
        throw new \InvalidArgumentException('Unsupported entity type for toPersistence in SocialProviderMapper');
    }

    /**
     * Create a new Eloquent model from a domain entity (for insert).
     */
    public static function toNewEloquent(DomainOAuthProvider $domain): EloquentOAuthProvider
    {
        return new EloquentOAuthProvider([
            'id' => $domain->getId()->get('value'),
            'social_provider' => $domain->getOAuthProvider()->value,
        ]);
    }

    /**
     * Map an iterable of EloquentOAuthProvider models to DomainOAuthProvider entities (generator).
     *
     * @param  iterable<EloquentOAuthProvider>  $models
     * @return \Generator<int, DomainOAuthProvider>
     */
    public static function toDomainIterable(iterable $models): \Generator
    {
        foreach ($models as $model) {
            yield self::toDomain($model);
        }
    }

    /**
     * Map an iterable of DomainOAuthProvider entities to EloquentOAuthProvider models (generator).
     *
     * @param  iterable<DomainOAuthProvider>  $domains
     * @return \Generator<int, EloquentOAuthProvider>
     */
    public static function toEloquentIterable(iterable $domains): \Generator
    {
        foreach ($domains as $domain) {
            yield self::toNewEloquent($domain);
        }
    }

    /**
     * Update an existing Eloquent model using a domain entity.
     */
    public static function toUpdatedEloquent(DomainOAuthProvider $domain, EloquentOAuthProvider $model): EloquentOAuthProvider
    {
        $model->id = $domain->getId()->get('value');
        $model->social_provider = $domain->getOAuthProvider()->value;

        return $model;
    }

    /**
     * Convert an Eloquent model to a domain entity.
     */
    public static function toDomain(object $model): object
    {
        if (! ($model instanceof EloquentOAuthProvider)) {
            throw new \InvalidArgumentException('Expected EloquentOAuthProvider in toDomain');
        }

        return DomainOAuthProvider::restore(
            new OAuthProviderId($model->id),
            OAuthProviderEnum::from($model->social_provider),
        );
    }

    /**
     * Convert a domain entity to a plain array (for presentation or serialization).
     */
    public static function toArray(DomainOAuthProvider $domain): array
    {
        return [
            'id' => $domain->getId()->get('value'),
            'social_provider' => $domain->getOAuthProvider()->value,
        ];
    }
}

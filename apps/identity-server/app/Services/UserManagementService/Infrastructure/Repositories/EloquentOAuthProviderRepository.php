<?php

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Entities\OAuthProvider;
use App\Services\UserManagementService\Domain\Repositories\OAuthProviderRepository;
use App\Services\UserManagementService\Infrastructure\Mappers\SocialProviderMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\SocialProvider as EloquentOAuthProvider;
use Zolta\Cqrs\Laravel\Eloquent\EloquentBaseRepository;
use Zolta\Domain\ValueObjects\OAuthProvider as EnumOAuthProvider;
use Zolta\Domain\ValueObjects\OAuthProviderId;

class EloquentOAuthProviderRepository extends EloquentBaseRepository implements OAuthProviderRepository
{
    protected function modelClass(): string
    {
        return EloquentOAuthProvider::class;
    }

    protected function getAllowedRelations(): array
    {
        return ['socialAccounts'];
    }

    public function findById(OAuthProviderId $id): ?OAuthProvider
    {
        $model = $this->show($id->get('value'));

        return $model ? SocialProviderMapper::toDomain($model) : null;
    }

    public function findByOAuthProvider(EnumOAuthProvider $oAuthProvider): ?OAuthProvider
    {
        $model = $this->findModelBy('social_provider', $oAuthProvider->value);

        return $model ? SocialProviderMapper::toDomain($model) : null;
    }

    public function saveOAuthProvider(OAuthProvider $oAuthProvider): void
    {
        $model = SocialProviderMapper::toNewEloquent($oAuthProvider);
        $this->create($model);
    }

    public function deleteOAuthProvider(OAuthProvider $oAuthProvider): void
    {
        $model = $this->findModelBy('id', $oAuthProvider->getId()->get('value'));
        if ($model) {
            $this->delete($model);
        }
    }

    public function updateOAuthProvider(OAuthProvider $oAuthProvider): void
    {
        $model = $this->show($oAuthProvider->getId()->get('value'));
        $model = SocialProviderMapper::toUpdatedEloquent($oAuthProvider, $model);
        $this->update($model);
    }

    /**
     * Locate a single model by column/value, including any allowed relations.
     */
    private function findModelBy(string $column, mixed $value): ?EloquentOAuthProvider
    {
        /** @var class-string<EloquentOAuthProvider> $model */
        $model = $this->modelClass();

        $query = $model::query()->where($column, $value);

        $relations = $this->getAllowedRelations();
        if ($relations !== []) {
            $query->with($relations);
        }

        return $query->first();
    }
}

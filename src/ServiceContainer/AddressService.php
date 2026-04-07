<?php

namespace Systha\Core\ServiceContainer;

use Illuminate\Database\Eloquent\Model;
use Systha\Core\DTO\AddressDto;
use Systha\Core\Models\AddressModel;


class AddressService
{
    public function createFor(Model $model, AddressDto $dto): AddressModel
    {
        // Optional: unset previous default
        $model->addresses()->update(['is_default' => false]);

        return $model->addresses()->create([
            ...$dto->toArray(),
            'is_default' => true,
        ]);
    }

    public function update(AddressModel $address, AddressDto $dto): AddressModel
    {
        $address->update($dto->toArray());

        return $address->refresh();
    }

    public function getDefault(Model $model): ?AddressModel
    {
        return $model->addresses()
            ->where('is_default', true)
            ->first();
    }

    public function createOrUpdateDefault(Model $model, AddressDto $dto): AddressModel
    {
        $address = $this->getDefault($model);

        if ($address) {
            return $this->update($address, $dto);
        }

        return $this->createFor($model, $dto);
    }

    public function setAsDefault(AddressModel $address): AddressModel
    {
        $model = $address->addressable;

        // remove previous default
        $model->addresses()->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        return $address->refresh();
    }
}
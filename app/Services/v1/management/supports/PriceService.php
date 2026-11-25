<?php

namespace App\Services\v1\management\supports;

use App\DTOs\v1\management\supports\PriceDTO;
use App\Models\Supports\PriceModel;

class PriceService
{
    /**
     * Crea un nuevo registro.
     *
     * @param PriceDTO $priceDTO
     * @return PriceModel
     */
    public function create(PriceDTO $priceDTO): PriceModel
    {
        return PriceModel::query()->create($priceDTO->toArray());
    }

    /**
     * Busca registro según id y retorna los datos.
     *
     * @param int $id
     * @return PriceModel
     */
    public function read(int $id): PriceModel
    {
        return PriceModel::query()->find($id);
    }

    /**
     * Actualiza la información de un registro según id.
     *
     * @param PriceModel $priceModel
     * @param PriceDTO $priceDTO
     * @return PriceModel
     */
    public function update(PriceModel $priceModel, PriceDTO $priceDTO): PriceModel
    {
        $priceModel->update($priceDTO->toArray());
        return $priceModel;
    }
}

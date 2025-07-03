<?php

namespace App\Services\v1\management\client;

use App\DTOs\v1\management\client\ClientDTO;
use App\Models\Clients\ClientModel;

class ClientGeneralDataService
{
    public function createClient(ClientDTO $clientData): ClientModel
    {
        return ClientModel::create($clientData->toArray());
    }

    public function updateClient(ClientModel $clientModel, ClientDTO $clientDTOData): ClientModel
    {
        $clientModel->update($clientDTOData->toArray());
        return $clientModel;
    }
}

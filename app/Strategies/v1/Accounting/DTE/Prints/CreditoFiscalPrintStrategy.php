<?php

namespace App\Strategies\v1\Accounting\DTE\Prints;

use App\Contracts\v1\Accounting\DTE\DTEPrinterInterface;
use App\Models\Accounting\DTEModel;

class CreditoFiscalPrintStrategy implements DTEPrinterInterface
{

    public function print(DTEModel $dte): string
    {
        // TODO: Implement print() method.
    }
}

<?php

namespace App\Libraries\Accounting\DTE;

class IssuerUtils
{
    /***
     * Nit de la empresa.
     * @return string
     */
    public function getNit(): string
    {
        return '1217-020621-101-4';
    }

    /***
     * NRC de la empresa
     * @return string
     */
    public function getNrc(): string
    {
        return '303483-9';
    }

    /***
     * Nombre de la empresa.
     * @return string
     */
    public function getName(): string
    {
        return 'NETPLUS COMPANY WORK S.A. DE C.V';
    }

    /***
     * ID del giro.
     * @return string
     */
    public function activityCode(): string
    {
        return '61109';
    }

    /***
     * Nombre del giro.
     * @return string
     */
    public function activityName(): string
    {
        return 'Servicio de Internet N.C.P.';
    }

    /***
     * Código del departamento.
     * @return int
     */
    public function getState(): int
    {
        return 12;
    }

    /***
     * Código del municipio.
     * @return int
     */
    public function getMunicipality(): int
    {
        return 22;
    }

    /***
     * Dirección de la empresa.
     * @return string
     */
    public function getAddress(): string
    {
        return 'Calle principal, Col. San Francisco, #34, San Miguel';
    }

    /***
     * Retorna número de teléfono.
     * @return string
     */
    public function getPhoneNumber(): string
    {
        return '7626-6022';
    }

    /***
     * Correo electrónico de la empresa.
     * @return string
     */
    public function getEmail(): string
    {
        return 'netpluscompanywork@gmail.com';
    }
}

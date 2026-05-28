<?php

namespace Boostly\Laravel\Contracts;

use Illuminate\Http\Request;

interface BoostlySecretResolver
{
    /**
     * Adja vissza a beérkező webhook-kéréshez tartozó secretet, vagy null-t,
     * ha ehhez a kéréshez nincs érvényes integráció.
     *
     * Multi-tenant host-appban innen oldható fel a tenant-specifikus titok
     * (pl. a kérés domainjéből), így a beépített route + middleware is
     * használható egyetlen globális secret helyett.
     */
    public function resolve(Request $request): ?string;
}

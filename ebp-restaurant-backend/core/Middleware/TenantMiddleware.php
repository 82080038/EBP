<?php

class TenantMiddleware
{

    public function validate($tenantId)
    {

        if (empty($tenantId)) {

            Response::error("Tenant ID is required");

        }



        return $tenantId;

    }

}

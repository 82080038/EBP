<?php

class TenantMiddleware
{

    public function validate($tenantId, $isPlatformOwner = false)
    {
        // Platform owners can access without tenant context
        if ($isPlatformOwner) {
            return null; // Return null to indicate platform-level access
        }

        if (empty($tenantId)) {

            Response::error("Tenant ID is required");

        }



        return $tenantId;

    }

}

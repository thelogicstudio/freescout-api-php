<?php

declare(strict_types=1);

namespace FreeScout\Api\Customers;

use FreeScout\Api\Endpoint;
use FreeScout\Api\Entity\PagedCollection;
use FreeScout\Api\Exception\ValidationErrorException;
use FreeScout\Api\Http\Hal\HalPagedResources;
use FreeScout\Api\Http\Hal\HalResource;

class CustomersEndpoint extends Endpoint
{
    /**
     * @throws ValidationErrorException
     */
    public function create(Customer $customer): ?int
    {
        return $this->restClient->createResource(
            $customer,
            '/customers'
        );
    }

    /**
     * @throws ValidationErrorException
     */
    public function update(Customer $customer): void
    {
        $this->restClient->updateResource(
            $customer,
            sprintf('/api/customers/%d', $customer->getId())
        );
    }

    public function get(int $id, ?CustomerRequest $customerRequest = null): Customer
    {
        $customerResource = $this->restClient->getResource(
            Customer::class,
            sprintf('/api/customers/%d', $id)
        );

        return $this->hydrateCustomerWithSubEntities(
            $customerResource,
            $customerRequest ?: new CustomerRequest()
        );
    }

    /**
     * @return Customer[]|PagedCollection
     */
    public function list(
        ?CustomerFilters $customerFilters = null,
        ?CustomerRequest $customerRequest = null
    ): PagedCollection {
        $uri = '/api/customers';
        if ($customerFilters) {
            $params = $customerFilters->getParams();
            if (!empty($params)) {
                $uri .= '?'.http_build_query($params);
            }
        }

        return $this->loadCustomers(
            $uri,
            $customerRequest ?: new CustomerRequest()
        );
    }

    public function delete(int $customerId): void
    {
        $this->restClient->deleteResource(
            sprintf('/api/customers/%d', $customerId)
        );
    }

    /**
     * @return Customer[]|PagedCollection
     */
    private function loadCustomers(
        string $uri,
        CustomerRequest $customerRequest
    ): PagedCollection {
        /** @var HalPagedResources $customerResources */
        $customerResources = $this->restClient->getResources(
            Customer::class,
            'customers',
            $uri
        );
        $customers = $customerResources->map(
            function (HalResource $customerResource) use ($customerRequest) {
                return $this->hydrateCustomerWithSubEntities($customerResource, $customerRequest);
            }
        );

        return new PagedCollection(
            $customers,
            $customerResources->getPageMetadata(),
            $customerResources->getLinks(),
            function (string $uri) use ($customerRequest) {
                return $this->loadCustomers($uri, $customerRequest);
            },
			$uri
        );
    }

    private function hydrateCustomerWithSubEntities(
        HalResource $customerResource,
        CustomerRequest $customerRequest
    ): Customer {
        $customerLoader = new CustomerLoader(
            $this->restClient,
            $customerResource,
            $customerRequest->getLinks()
        );
        $customerLoader->load();

        return $customerResource->getEntity();
    }
}

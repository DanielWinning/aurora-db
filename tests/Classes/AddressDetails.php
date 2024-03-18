<?php

namespace Luma\Tests\Classes;

use Luma\AuroraDatabase\Attributes\Column;
use Luma\AuroraDatabase\Attributes\Identifier;
use Luma\AuroraDatabase\Attributes\Schema;
use Luma\AuroraDatabase\Model\Aurora;

#[Schema('DatabaseComponentTest')]
class AddressDetails extends Aurora
{
    #[Identifier]
    #[Column('intAddressDetailsId')]
    protected int $id;

    #[Column('strAddressLineOne')]
    private string $addressLineOne;

    #[Column('strAddressLineTwo')]
    private ?string $addressLineTwo;

    #[Column('strCity')]
    private string $city;

    #[Column('strPostcode')]
    private string $postcode;

    #[Column('intUserId')]
    private User $user;

    /**
     * @return string
     */
    public function getAddressLineOne(): string
    {
        return $this->addressLineOne;
    }
}
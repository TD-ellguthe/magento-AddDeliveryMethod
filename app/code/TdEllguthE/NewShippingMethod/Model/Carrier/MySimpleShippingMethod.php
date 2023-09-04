<?php
/**
 * Copyright (c) 2023 TechDivision GmbH <info@techdivision.com> - TechDivision GmbH
 * All rights reserved
 *
 * This product includes proprietary software developed at TechDivision GmbH, Germany
 * For more information see http://www.techdivision.com/
 *
 * To obtain a valid license for using this software please contact us at
 * license@techdivision.com
 */

declare(strict_types=1);

namespace TdEllguthE\NewDeliveryMethod\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * @copyright  Copyright (c) 2023 TechDivision GmbH <info@techdivision.com> - TechDivision GmbH
 *
 * @link       https://www.techdivision.com/
 * @author     Team Zero <zero@techdivision.com>
 */
class MySimpleShippingMethod extends AbstractCarrier
{
    public const CARRIER_NAME = self::CODE;
    public const METHOD_NAME = self::CODE;
    private const CODE = 'mySimpleShippingMethod';

    /** @var MethodFactory */
    protected MethodFactory $rateMethodFactory;

    /** @var ResultFactory */
    private ResultFactory $rateResultFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param MethodFactory $rateMethodFactory
     * @param ResultFactory $rateResultFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        MethodFactory $rateMethodFactory,
        ResultFactory $rateResultFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->rateMethodFactory = $rateMethodFactory;
        $this->rateResultFactory = $rateResultFactory;
        $this->_code = self::CODE;
    }

    /**
     * @inheritdoc
     * @return string[]
     */
    public function getAllowedMethods(): array
    {
        return [
            $this->_code => $this->getConfigData('name'),
        ];
    }

    /**
     * @param RateRequest $request
     * @return false|Result
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collectRates(RateRequest $request): ?Result
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var Result $result */
        $result = $this->rateResultFactory->create();
        $shippingMethod = $this->generateShippingMethod();

        $result->append($shippingMethod);

        return $result;
    }

    /**
     * @return string
     */
    public function getCarrierName(): string
    {
        return self::CARRIER_NAME;
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    /**
     * @return Method
     */
    private function generateShippingMethod(): Method
    {
        /** @var Method $method */
        $method = $this->rateMethodFactory->create();
        $price = (float) $this->getConfigData('price');

        $method->setCarrier($this->getCarrierName());
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->getMethodName());
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setCost($price);  // shipping
        $method->setPrice($price); // shipping + handling

        return $method;
    }
}

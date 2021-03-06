<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\ForceCustomerLogin\Setup;

use \Magento\Eav\Setup\EavSetup;
use \Magento\Eav\Setup\EavSetupFactory;
use \Magento\Framework\Setup\UpgradeDataInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->runUpgrade101($setup);
        }

        if (version_compare($context->getVersion(), '1.2.4', '<')) {
            $this->runUpgrade124($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    protected function runUpgrade101(ModuleDataSetupInterface $setup)
    {
        $whitelistEntries = [
            $this->getWhitelistEntryAsArray(0, 'Rest API', '/rest'),
            $this->getWhitelistEntryAsArray(0, 'Customer Account Login', '/customer/account/login'),
            $this->getWhitelistEntryAsArray(0, 'Customer Account Logout', '/customer/account/logout'),
            $this->getWhitelistEntryAsArray(0, 'Customer Account Logout Success', '/customer/account/logoutSuccess'),
            $this->getWhitelistEntryAsArray(0, 'Customer Account Create', '/customer/account/create'),
            $this->getWhitelistEntryAsArray(0, 'Customer Account Create Password', '/customer/account/createPassword'),
            $this->getWhitelistEntryAsArray(0, 'Customer Account Forgot Password', '/customer/account/forgotpassword'),
            $this->getWhitelistEntryAsArray(
                0,
                'Customer Account Forgot Password Post',
                '/customer/account/forgotpasswordpost'
            ),
            $this->getWhitelistEntryAsArray(0, 'Customer Section Load', '/customer/section/load'),
            $this->getWhitelistEntryAsArray(0, 'Contact Us', '/contact', true),
            $this->getWhitelistEntryAsArray(0, 'Help', '/help', true)
        ];

        $setup->getConnection()->insertMultiple(
            $setup->getTable('bitexpert_forcelogin_whitelist'),
            $whitelistEntries
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    protected function runUpgrade124(ModuleDataSetupInterface $setup)
    {
        $bind = [
            'editable' => true
        ];

        $urlRules = [
            '/rest',
            '/customer/account/login'
        ];

        // All routes can be editable except for rest and login page
        $where = [
            'url_rule NOT IN(?)' => $urlRules
        ];

        $setup->getConnection()->update(
            $setup->getTable('bitexpert_forcelogin_whitelist'),
            $bind,
            $where
        );
    }


    /**
     * @param int $storeId
     * @param string $label
     * @param string $urlRule
     * @param boolean $editable
     * @return array
     */
    protected function getWhitelistEntryAsArray(
        $storeId,
        $label,
        $urlRule,
        $editable = false
    ) {
        return array(
            'store_id' => $storeId,
            'label' => $label,
            'url_rule' => $urlRule,
            'editable' => $editable
        );
    }
}

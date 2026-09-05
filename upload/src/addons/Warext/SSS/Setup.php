<?php

namespace Warext\SSS;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1(): void
    {
        $this->schemaManager()->createTable('xf_wrxt_sss_category', function(Create $table)
        {
            $table->addColumn('category_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('title', 'varchar', 100);
            $table->addColumn('description', 'varchar', 255)->setDefault('');
            $table->addColumn('icon', 'varchar', 64)->setDefault('fa-circle-question');
            $table->addColumn('display_order', 'int')->unsigned()->setDefault(10);
            $table->addColumn('is_active', 'tinyint')->unsigned()->setDefault(1);
            $table->addColumn('created_date', 'int')->unsigned();
            $table->addColumn('updated_date', 'int')->unsigned()->setDefault(0);
            $table->addPrimaryKey('category_id');
            $table->addKey(['is_active', 'display_order'], 'active_order');
        });
    }

    public function installStep2(): void
    {
        $this->schemaManager()->createTable('xf_wrxt_sss_faq', function(Create $table)
        {
            $table->addColumn('faq_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('category_id', 'int')->unsigned();
            $table->addColumn('question', 'varchar', 255);
            $table->addColumn('answer', 'mediumtext');
            $table->addColumn('anchor', 'varchar', 100)->setDefault('');
            $table->addColumn('display_order', 'int')->unsigned()->setDefault(10);
            $table->addColumn('is_active', 'tinyint')->unsigned()->setDefault(1);
            $table->addColumn('is_featured', 'tinyint')->unsigned()->setDefault(0);
            $table->addColumn('created_date', 'int')->unsigned();
            $table->addColumn('updated_date', 'int')->unsigned()->setDefault(0);
            $table->addPrimaryKey('faq_id');
            $table->addKey(['category_id', 'is_active', 'display_order'], 'category_active_order');
            $table->addKey(['is_featured', 'is_active'], 'featured_active');
            $table->addUniqueKey('anchor');
        });
    }

    public function postInstall(array &$stateChanges): void
    {
        foreach ([1, 2, 3, 4] as $userGroupId)
        {
            $this->applyViewPermission($userGroupId);
        }
    }

    protected function applyViewPermission(int $userGroupId): void
    {
        $userGroup = \XF::em()->find('XF:UserGroup', $userGroupId);
        if (!$userGroup)
        {
            return;
        }

        $permissionRepo = \XF::repository('XF:PermissionEntry');
        $existing = $permissionRepo->getGlobalUserGroupPermissionEntries($userGroupId);
        $configured = $existing['wrxtSss'] ?? [];

        if (array_key_exists('view', $configured))
        {
            return;
        }

        $service = \XF::service('XF:UpdatePermissions');
        $service->setUserGroup($userGroup);
        $service->setGlobal();
        $service->updatePermissions(['wrxtSss' => ['view' => 'allow']]);
    }

    public function uninstallStep1(): void
    {
        $this->schemaManager()->dropTable('xf_wrxt_sss_faq');
    }

    public function uninstallStep2(): void
    {
        $this->schemaManager()->dropTable('xf_wrxt_sss_category');
    }
}

<?php

use Phinx\Migration\AbstractMigration;

class ShareModule extends AbstractMigration {
    /**
     * Change Method.
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * Uncomment this method if you would like to use it.
     * public function change()
     * {
     * }
     */

    /**
     * Migrate Up.
     */
    public function up() {
        $table = $this->table('share_access_level',
            ['id' => false, 'primary_key' => ['smap_id', 'group_id', 'right_id']]);
        $table->addColumn('smap_id', 'integer', ['signed' => false, 'length' => 10])
            ->addColumn('group_id', 'integer', ['signed' => false, 'length' => 10])
            ->addColumn('right_id', 'integer', ['signed' => false, 'length' => 10])
            ->addIndex(['group_id'])
            ->addIndex(['right_id'])
            ->save();

        $table = $this->table('share_domain2site',
            ['id' => false, 'primary_key' => ['domain_id', 'site_id']]);
        $table->addColumn('domain_id', 'integer', ['signed' => false, 'length' => 10])
            ->addColumn('site_id', 'integer', ['signed' => false, 'length' => 10])
            ->addIndex(['site_id'])
            ->save();

        $table = $this->table('share_domains',
            ['id' => false, 'primary_key' => ['domain_id']]);
        $table->addColumn('domain_id', 'integer', ['signed' => false, 'length' => 10])
            ->addColumn('domain_protocol', 'string', ['length' => 5, 'default' => 'htpp'])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down() {

        !$this->table('share_domains')->exists() || $this->table('share_domains')->drop();
        !$this->table('share_domain2site')->exists() || $this->table('share_domain2site')->drop();
        !$this->table('share_access_level')->exists() || $this->table('share_access_level')->drop();

    }
}
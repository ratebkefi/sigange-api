<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210118000000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE api_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, user_group_id INT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) DEFAULT NULL, token VARCHAR(255) NOT NULL, expired_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_7BA2F5EB77153098 (code), UNIQUE INDEX UNIQ_7BA2F5EB5F37A13B (token), INDEX IDX_7BA2F5EBA76ED395 (user_id), INDEX IDX_7BA2F5EB1ED93D47 (user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_81398E0977153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device (id INT AUTO_INCREMENT NOT NULL, user_group_id INT DEFAULT NULL, model_id INT NOT NULL, site_id INT DEFAULT NULL, network_id INT DEFAULT NULL, platform_id INT DEFAULT NULL, status_id INT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, enabled TINYINT(1) NOT NULL, comment LONGTEXT DEFAULT NULL, internal_comment LONGTEXT DEFAULT NULL, serial_number VARCHAR(255) DEFAULT NULL, mac_address VARCHAR(17) NOT NULL, os_version VARCHAR(10) DEFAULT NULL, wanted_os_version VARCHAR(10) DEFAULT NULL, software_version VARCHAR(10) DEFAULT NULL, wanted_software_version VARCHAR(10) DEFAULT NULL, is_ssh_enabled TINYINT(1) NOT NULL, is_vpn_enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_92FB68E77153098 (code), INDEX IDX_92FB68E1ED93D47 (user_group_id), INDEX IDX_92FB68E7975B7E7 (model_id), INDEX IDX_92FB68EF6BD1646 (site_id), INDEX IDX_92FB68E34128B91 (network_id), INDEX IDX_92FB68EFFE6496F (platform_id), INDEX IDX_92FB68E6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device_tag (device_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_E9776D1A94A4C7D4 (device_id), INDEX IDX_E9776D1ABAD26311 (tag_id), PRIMARY KEY(device_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device_diagnostic (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ip_v4 VARCHAR(15) DEFAULT NULL, ip_v6 VARCHAR(46) DEFAULT NULL, last_ping_at DATETIME DEFAULT NULL, last_http_connection_at DATETIME DEFAULT NULL, last_ssh_connection_at DATETIME DEFAULT NULL, last_vpn_connection_at DATETIME DEFAULT NULL, last_websocket_connection_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_7D4FCB4F77153098 (code), UNIQUE INDEX UNIQ_7D4FCB4F94A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device_model (id INT AUTO_INCREMENT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_111092BE77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device_model_output (id INT AUTO_INCREMENT NOT NULL, model_id INT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, number INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_9D4CD7D077153098 (code), INDEX IDX_9D4CD7D07975B7E7 (model_id), UNIQUE INDEX UNIQ_9D4CD7D07975B7E796901F54 (model_id, number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device_output (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, model_output_id INT NOT NULL, video_stream_id INT DEFAULT NULL, video_overlay_id INT DEFAULT NULL, template_model_output_id INT DEFAULT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1FCE658577153098 (code), INDEX IDX_1FCE658594A4C7D4 (device_id), INDEX IDX_1FCE6585E7C01113 (model_output_id), INDEX IDX_1FCE658512923BAB (video_stream_id), INDEX IDX_1FCE658577D79857 (video_overlay_id), INDEX IDX_1FCE65851A07F997 (template_model_output_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device_status (id INT AUTO_INCREMENT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_A810140777153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entity_display_customization (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, shared_with_id INT DEFAULT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, columns JSON NOT NULL, entity_class_name VARCHAR(255) NOT NULL, is_default TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_7950F24E77153098 (code), INDEX IDX_7950F24E7E3C61F9 (owner_id), INDEX IDX_7950F24ED14FE63F (shared_with_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE network (id INT AUTO_INCREMENT NOT NULL, user_group_id INT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, public_ip_v4 VARCHAR(15) DEFAULT NULL, public_ip_v6 VARCHAR(46) DEFAULT NULL, gateway_ip_v4 VARCHAR(15) DEFAULT NULL, gateway_ip_v6 VARCHAR(46) DEFAULT NULL, ssh VARCHAR(255) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_608487BC77153098 (code), INDEX IDX_608487BC1ED93D47 (user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE network_site (network_id INT NOT NULL, site_id INT NOT NULL, INDEX IDX_121ECA9334128B91 (network_id), INDEX IDX_121ECA93F6BD1646 (site_id), PRIMARY KEY(network_id, site_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE network_tag (network_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_EF616E4934128B91 (network_id), INDEX IDX_EF616E49BAD26311 (tag_id), PRIMARY KEY(network_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE platform (id INT AUTO_INCREMENT NOT NULL, user_group_id INT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, api_http_url VARCHAR(255) DEFAULT NULL, api_http_proxy VARCHAR(255) DEFAULT NULL, api_socket_url VARCHAR(255) DEFAULT NULL, color VARCHAR(6) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_3952D0CB77153098 (code), INDEX IDX_3952D0CB1ED93D47 (user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE screen (id INT AUTO_INCREMENT NOT NULL, device_output_id INT DEFAULT NULL, site_id INT DEFAULT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_DF4C613077153098 (code), UNIQUE INDEX UNIQ_DF4C6130E5E96C11 (device_output_id), INDEX IDX_DF4C6130F6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE screen_tag (screen_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_BD7C388341A67722 (screen_id), INDEX IDX_BD7C3883BAD26311 (tag_id), PRIMARY KEY(screen_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, user_group_id INT NOT NULL, template_model_id INT DEFAULT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, external_id VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, enabled TINYINT(1) NOT NULL, address_country VARCHAR(50) DEFAULT NULL, address_city VARCHAR(100) DEFAULT NULL, address_zip_code VARCHAR(10) DEFAULT NULL, address_street VARCHAR(255) DEFAULT NULL, contact_gender VARCHAR(1) DEFAULT NULL, contact_first_name VARCHAR(100) DEFAULT NULL, contact_last_name VARCHAR(100) DEFAULT NULL, contact_email VARCHAR(255) DEFAULT NULL, contact_phone VARCHAR(20) DEFAULT NULL, UNIQUE INDEX UNIQ_694309E477153098 (code), INDEX IDX_694309E41ED93D47 (user_group_id), INDEX IDX_694309E426238794 (template_model_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site_tag (site_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_F71486A3F6BD1646 (site_id), INDEX IDX_F71486A3BAD26311 (tag_id), PRIMARY KEY(site_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, tag_group_id INT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_389B78377153098 (code), INDEX IDX_389B783C865A29C (tag_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_group (id INT AUTO_INCREMENT NOT NULL, user_group_id INT DEFAULT NULL, target_id INT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, options JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_4F2C5DC377153098 (code), INDEX IDX_4F2C5DC31ED93D47 (user_group_id), INDEX IDX_4F2C5DC3158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_target (id INT AUTO_INCREMENT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(25) NOT NULL, description LONGTEXT DEFAULT NULL, entity_class_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_AF2E66D077153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE template_model (id INT AUTO_INCREMENT NOT NULL, user_group_id INT DEFAULT NULL, platform_id INT DEFAULT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_E443A11677153098 (code), INDEX IDX_E443A116FFE6496F (platform_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE template_model_output (id INT AUTO_INCREMENT NOT NULL, template_model_id INT NOT NULL, video_stream_id INT DEFAULT NULL, video_overlay_id INT DEFAULT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', number INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_A5ED6C5477153098 (code), INDEX IDX_A5ED6C5426238794 (template_model_id), INDEX IDX_A5ED6C5412923BAB (video_stream_id), INDEX IDX_A5ED6C5477D79857 (video_overlay_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_super_admin TINYINT(1) DEFAULT \'0\' NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, activated_token VARCHAR(255) DEFAULT NULL, activated_token_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D64977153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_user_role (user_id INT NOT NULL, user_role_id INT NOT NULL, INDEX IDX_2D084B47A76ED395 (user_id), INDEX IDX_2D084B478E0E3CA6 (user_role_id), PRIMARY KEY(user_id, user_role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_user_group (user_id INT NOT NULL, user_group_id INT NOT NULL, INDEX IDX_28657971A76ED395 (user_id), INDEX IDX_286579711ED93D47 (user_group_id), PRIMARY KEY(user_id, user_group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_group (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8F02BF9D77153098 (code), INDEX IDX_8F02BF9D9395C3F3 (customer_id), INDEX IDX_8F02BF9D727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_group_user_role (user_group_id INT NOT NULL, user_role_id INT NOT NULL, INDEX IDX_FF87F6CD1ED93D47 (user_group_id), INDEX IDX_FF87F6CD8E0E3CA6 (user_role_id), PRIMARY KEY(user_group_id, user_role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role (id INT AUTO_INCREMENT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', role_name VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_2DE8C6A377153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_overlay (id INT AUTO_INCREMENT NOT NULL, user_group_id INT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_5C12D51377153098 (code), INDEX IDX_5C12D5131ED93D47 (user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_overlay_tag (video_overlay_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_9D25CCDF77D79857 (video_overlay_id), INDEX IDX_9D25CCDFBAD26311 (tag_id), PRIMARY KEY(video_overlay_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_stream (id INT AUTO_INCREMENT NOT NULL, user_group_id INT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, url VARCHAR(2500) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_C8BE1ED177153098 (code), INDEX IDX_C8BE1ED11ED93D47 (user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_stream_tag (video_stream_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_294B374B12923BAB (video_stream_id), INDEX IDX_294B374BBAD26311 (tag_id), PRIMARY KEY(video_stream_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE webhook (id INT AUTO_INCREMENT NOT NULL, user_group_id INT NOT NULL, code BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, event_type VARCHAR(255) NOT NULL, resource_class VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8A74175677153098 (code), INDEX IDX_8A7417561ED93D47 (user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EB1ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E1ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E7975B7E7 FOREIGN KEY (model_id) REFERENCES device_model (id)');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68EF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E34128B91 FOREIGN KEY (network_id) REFERENCES network (id)');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68EFFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id)');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E6BF700BD FOREIGN KEY (status_id) REFERENCES device_status (id)');
        $this->addSql('ALTER TABLE device_tag ADD CONSTRAINT FK_E9776D1A94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE device_tag ADD CONSTRAINT FK_E9776D1ABAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE device_diagnostic ADD CONSTRAINT FK_7D4FCB4F94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id)');
        $this->addSql('ALTER TABLE device_model_output ADD CONSTRAINT FK_9D4CD7D07975B7E7 FOREIGN KEY (model_id) REFERENCES device_model (id)');
        $this->addSql('ALTER TABLE device_output ADD CONSTRAINT FK_1FCE658594A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id)');
        $this->addSql('ALTER TABLE device_output ADD CONSTRAINT FK_1FCE6585E7C01113 FOREIGN KEY (model_output_id) REFERENCES device_model_output (id)');
        $this->addSql('ALTER TABLE device_output ADD CONSTRAINT FK_1FCE658512923BAB FOREIGN KEY (video_stream_id) REFERENCES video_stream (id)');
        $this->addSql('ALTER TABLE device_output ADD CONSTRAINT FK_1FCE658577D79857 FOREIGN KEY (video_overlay_id) REFERENCES video_overlay (id)');
        $this->addSql('ALTER TABLE device_output ADD CONSTRAINT FK_1FCE65851A07F997 FOREIGN KEY (template_model_output_id) REFERENCES template_model_output (id)');
        $this->addSql('ALTER TABLE entity_display_customization ADD CONSTRAINT FK_7950F24E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE entity_display_customization ADD CONSTRAINT FK_7950F24ED14FE63F FOREIGN KEY (shared_with_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE network ADD CONSTRAINT FK_608487BC1ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE network_site ADD CONSTRAINT FK_121ECA9334128B91 FOREIGN KEY (network_id) REFERENCES network (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE network_site ADD CONSTRAINT FK_121ECA93F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE network_tag ADD CONSTRAINT FK_EF616E4934128B91 FOREIGN KEY (network_id) REFERENCES network (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE network_tag ADD CONSTRAINT FK_EF616E49BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE platform ADD CONSTRAINT FK_3952D0CB1ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE screen ADD CONSTRAINT FK_DF4C6130E5E96C11 FOREIGN KEY (device_output_id) REFERENCES device_output (id)');
        $this->addSql('ALTER TABLE screen ADD CONSTRAINT FK_DF4C6130F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE screen_tag ADD CONSTRAINT FK_BD7C388341A67722 FOREIGN KEY (screen_id) REFERENCES screen (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE screen_tag ADD CONSTRAINT FK_BD7C3883BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E41ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E426238794 FOREIGN KEY (template_model_id) REFERENCES template_model (id)');
        $this->addSql('ALTER TABLE site_tag ADD CONSTRAINT FK_F71486A3F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site_tag ADD CONSTRAINT FK_F71486A3BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B783C865A29C FOREIGN KEY (tag_group_id) REFERENCES tag_group (id)');
        $this->addSql('ALTER TABLE tag_group ADD CONSTRAINT FK_4F2C5DC31ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE tag_group ADD CONSTRAINT FK_4F2C5DC3158E0B66 FOREIGN KEY (target_id) REFERENCES tag_target (id)');
        $this->addSql('ALTER TABLE template_model ADD CONSTRAINT FK_E443A116FFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id)');
        $this->addSql('ALTER TABLE template_model_output ADD CONSTRAINT FK_A5ED6C5426238794 FOREIGN KEY (template_model_id) REFERENCES template_model (id)');
        $this->addSql('ALTER TABLE template_model_output ADD CONSTRAINT FK_A5ED6C5412923BAB FOREIGN KEY (video_stream_id) REFERENCES video_stream (id)');
        $this->addSql('ALTER TABLE template_model_output ADD CONSTRAINT FK_A5ED6C5477D79857 FOREIGN KEY (video_overlay_id) REFERENCES video_overlay (id)');
        $this->addSql('ALTER TABLE user_user_role ADD CONSTRAINT FK_2D084B47A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_user_role ADD CONSTRAINT FK_2D084B478E0E3CA6 FOREIGN KEY (user_role_id) REFERENCES user_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_user_group ADD CONSTRAINT FK_28657971A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_user_group ADD CONSTRAINT FK_286579711ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_group ADD CONSTRAINT FK_8F02BF9D9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE user_group ADD CONSTRAINT FK_8F02BF9D727ACA70 FOREIGN KEY (parent_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE user_group_user_role ADD CONSTRAINT FK_FF87F6CD1ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_group_user_role ADD CONSTRAINT FK_FF87F6CD8E0E3CA6 FOREIGN KEY (user_role_id) REFERENCES user_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_overlay ADD CONSTRAINT FK_5C12D5131ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE video_overlay_tag ADD CONSTRAINT FK_9D25CCDF77D79857 FOREIGN KEY (video_overlay_id) REFERENCES video_overlay (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_overlay_tag ADD CONSTRAINT FK_9D25CCDFBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_stream ADD CONSTRAINT FK_C8BE1ED11ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE video_stream_tag ADD CONSTRAINT FK_294B374B12923BAB FOREIGN KEY (video_stream_id) REFERENCES video_stream (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_stream_tag ADD CONSTRAINT FK_294B374BBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webhook ADD CONSTRAINT FK_8A7417561ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE template_model ADD CONSTRAINT FK_E443A1161ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id)');
        $this->addSql('CREATE INDEX IDX_E443A1161ED93D47 ON template_model (user_group_id)');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_group DROP FOREIGN KEY FK_8F02BF9D9395C3F3');
        $this->addSql('ALTER TABLE device_tag DROP FOREIGN KEY FK_E9776D1A94A4C7D4');
        $this->addSql('ALTER TABLE device_diagnostic DROP FOREIGN KEY FK_7D4FCB4F94A4C7D4');
        $this->addSql('ALTER TABLE device_output DROP FOREIGN KEY FK_1FCE658594A4C7D4');
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68E7975B7E7');
        $this->addSql('ALTER TABLE device_model_output DROP FOREIGN KEY FK_9D4CD7D07975B7E7');
        $this->addSql('ALTER TABLE device_output DROP FOREIGN KEY FK_1FCE6585E7C01113');
        $this->addSql('ALTER TABLE screen DROP FOREIGN KEY FK_DF4C6130E5E96C11');
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68E6BF700BD');
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68E34128B91');
        $this->addSql('ALTER TABLE network_site DROP FOREIGN KEY FK_121ECA9334128B91');
        $this->addSql('ALTER TABLE network_tag DROP FOREIGN KEY FK_EF616E4934128B91');
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68EFFE6496F');
        $this->addSql('ALTER TABLE template_model DROP FOREIGN KEY FK_E443A116FFE6496F');
        $this->addSql('ALTER TABLE screen_tag DROP FOREIGN KEY FK_BD7C388341A67722');
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68EF6BD1646');
        $this->addSql('ALTER TABLE network_site DROP FOREIGN KEY FK_121ECA93F6BD1646');
        $this->addSql('ALTER TABLE screen DROP FOREIGN KEY FK_DF4C6130F6BD1646');
        $this->addSql('ALTER TABLE site_tag DROP FOREIGN KEY FK_F71486A3F6BD1646');
        $this->addSql('ALTER TABLE device_tag DROP FOREIGN KEY FK_E9776D1ABAD26311');
        $this->addSql('ALTER TABLE network_tag DROP FOREIGN KEY FK_EF616E49BAD26311');
        $this->addSql('ALTER TABLE screen_tag DROP FOREIGN KEY FK_BD7C3883BAD26311');
        $this->addSql('ALTER TABLE site_tag DROP FOREIGN KEY FK_F71486A3BAD26311');
        $this->addSql('ALTER TABLE video_overlay_tag DROP FOREIGN KEY FK_9D25CCDFBAD26311');
        $this->addSql('ALTER TABLE video_stream_tag DROP FOREIGN KEY FK_294B374BBAD26311');
        $this->addSql('ALTER TABLE tag DROP FOREIGN KEY FK_389B783C865A29C');
        $this->addSql('ALTER TABLE tag_group DROP FOREIGN KEY FK_4F2C5DC3158E0B66');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E426238794');
        $this->addSql('ALTER TABLE template_model_output DROP FOREIGN KEY FK_A5ED6C5426238794');
        $this->addSql('ALTER TABLE device_output DROP FOREIGN KEY FK_1FCE65851A07F997');
        $this->addSql('ALTER TABLE api_token DROP FOREIGN KEY FK_7BA2F5EBA76ED395');
        $this->addSql('ALTER TABLE entity_display_customization DROP FOREIGN KEY FK_7950F24E7E3C61F9');
        $this->addSql('ALTER TABLE user_user_role DROP FOREIGN KEY FK_2D084B47A76ED395');
        $this->addSql('ALTER TABLE user_user_group DROP FOREIGN KEY FK_28657971A76ED395');
        $this->addSql('ALTER TABLE api_token DROP FOREIGN KEY FK_7BA2F5EB1ED93D47');
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68E1ED93D47');
        $this->addSql('ALTER TABLE entity_display_customization DROP FOREIGN KEY FK_7950F24ED14FE63F');
        $this->addSql('ALTER TABLE network DROP FOREIGN KEY FK_608487BC1ED93D47');
        $this->addSql('ALTER TABLE platform DROP FOREIGN KEY FK_3952D0CB1ED93D47');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E41ED93D47');
        $this->addSql('ALTER TABLE tag_group DROP FOREIGN KEY FK_4F2C5DC31ED93D47');
        $this->addSql('ALTER TABLE user_user_group DROP FOREIGN KEY FK_286579711ED93D47');
        $this->addSql('ALTER TABLE user_group DROP FOREIGN KEY FK_8F02BF9D727ACA70');
        $this->addSql('ALTER TABLE user_group_user_role DROP FOREIGN KEY FK_FF87F6CD1ED93D47');
        $this->addSql('ALTER TABLE video_overlay DROP FOREIGN KEY FK_5C12D5131ED93D47');
        $this->addSql('ALTER TABLE video_stream DROP FOREIGN KEY FK_C8BE1ED11ED93D47');
        $this->addSql('ALTER TABLE webhook DROP FOREIGN KEY FK_8A7417561ED93D47');
        $this->addSql('ALTER TABLE user_user_role DROP FOREIGN KEY FK_2D084B478E0E3CA6');
        $this->addSql('ALTER TABLE user_group_user_role DROP FOREIGN KEY FK_FF87F6CD8E0E3CA6');
        $this->addSql('ALTER TABLE device_output DROP FOREIGN KEY FK_1FCE658577D79857');
        $this->addSql('ALTER TABLE template_model_output DROP FOREIGN KEY FK_A5ED6C5477D79857');
        $this->addSql('ALTER TABLE video_overlay_tag DROP FOREIGN KEY FK_9D25CCDF77D79857');
        $this->addSql('ALTER TABLE device_output DROP FOREIGN KEY FK_1FCE658512923BAB');
        $this->addSql('ALTER TABLE template_model_output DROP FOREIGN KEY FK_A5ED6C5412923BAB');
        $this->addSql('ALTER TABLE video_stream_tag DROP FOREIGN KEY FK_294B374B12923BAB');
        $this->addSql('DROP TABLE api_token');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE device');
        $this->addSql('DROP TABLE device_tag');
        $this->addSql('DROP TABLE device_diagnostic');
        $this->addSql('DROP TABLE device_model');
        $this->addSql('DROP TABLE device_model_output');
        $this->addSql('DROP TABLE device_output');
        $this->addSql('DROP TABLE device_status');
        $this->addSql('DROP TABLE entity_display_customization');
        $this->addSql('DROP TABLE network');
        $this->addSql('DROP TABLE network_site');
        $this->addSql('DROP TABLE network_tag');
        $this->addSql('DROP TABLE platform');
        $this->addSql('DROP TABLE screen');
        $this->addSql('DROP TABLE screen_tag');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE site_tag');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_group');
        $this->addSql('DROP TABLE tag_target');
        $this->addSql('DROP TABLE template_model');
        $this->addSql('DROP TABLE template_model_output');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_user_role');
        $this->addSql('DROP TABLE user_user_group');
        $this->addSql('DROP TABLE user_group');
        $this->addSql('DROP TABLE user_group_user_role');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE video_overlay');
        $this->addSql('DROP TABLE video_overlay_tag');
        $this->addSql('DROP TABLE video_stream');
        $this->addSql('DROP TABLE video_stream_tag');
        $this->addSql('DROP TABLE webhook');
    }
}

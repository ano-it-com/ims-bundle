<?= "<?php\n"; ?>

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class <?= $className ?> extends AbstractMigration
{

    public function getDescription(): string
    {
        return '';
    }


    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE ims_files_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ims_actions_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ims_action_statuses_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ims_action_tasks_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ims_action_task_statuses_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ims_action_task_types_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ims_categories_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ims_comments_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ims_incidents_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ims_incident_statuses_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ims_locations_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ims_group_permissions_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ims_permissions_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE ims_files (id INT NOT NULL, created_by_id INT NOT NULL, owner_code VARCHAR(255) NOT NULL, owner_id INT NOT NULL, path TEXT NOT NULL, original_name TEXT NOT NULL, size INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BD6851EBB03A8386 ON ims_files (created_by_id)');
        $this->addSql('CREATE INDEX fileable_idx ON ims_files (owner_code, owner_id, deleted)');
        $this->addSql('CREATE TABLE ims_actions (id INT NOT NULL, status_id INT DEFAULT NULL, incident_id INT NOT NULL, responsible_group_id INT NOT NULL, responsible_user_id INT DEFAULT NULL, created_by_id INT NOT NULL, updated_by_id INT NOT NULL, title TEXT NOT NULL, description TEXT DEFAULT NULL, code VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B90BA67A6BF700BD ON ims_actions (status_id)');
        $this->addSql('CREATE INDEX IDX_B90BA67A59E53FB9 ON ims_actions (incident_id)');
        $this->addSql('CREATE INDEX IDX_B90BA67A80FF6630 ON ims_actions (responsible_group_id)');
        $this->addSql('CREATE INDEX IDX_B90BA67ABDAD1998 ON ims_actions (responsible_user_id)');
        $this->addSql('CREATE INDEX IDX_B90BA67AB03A8386 ON ims_actions (created_by_id)');
        $this->addSql('CREATE INDEX IDX_B90BA67A896DBBDE ON ims_actions (updated_by_id)');
        $this->addSql('CREATE TABLE ims_action_statuses (id INT NOT NULL, created_by_id INT NOT NULL, action_id INT NOT NULL, responsible_group_id INT NOT NULL, responsible_user_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_41CE9E0CB03A8386 ON ims_action_statuses (created_by_id)');
        $this->addSql('CREATE INDEX IDX_41CE9E0C9D32F035 ON ims_action_statuses (action_id)');
        $this->addSql('CREATE INDEX IDX_41CE9E0C80FF6630 ON ims_action_statuses (responsible_group_id)');
        $this->addSql('CREATE INDEX IDX_41CE9E0CBDAD1998 ON ims_action_statuses (responsible_user_id)');
        $this->addSql('CREATE TABLE ims_action_tasks (id INT NOT NULL, action_id INT NOT NULL, type_id INT NOT NULL, status_id INT DEFAULT NULL, created_by_id INT NOT NULL, updated_by_id INT NOT NULL, input_data JSONB DEFAULT NULL, report_data JSONB DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C206E47D9D32F035 ON ims_action_tasks (action_id)');
        $this->addSql('CREATE INDEX IDX_C206E47DC54C8C93 ON ims_action_tasks (type_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C206E47D6BF700BD ON ims_action_tasks (status_id)');
        $this->addSql('CREATE INDEX IDX_C206E47DB03A8386 ON ims_action_tasks (created_by_id)');
        $this->addSql('CREATE INDEX IDX_C206E47D896DBBDE ON ims_action_tasks (updated_by_id)');
        $this->addSql('CREATE TABLE ims_action_task_statuses (id INT NOT NULL, created_by_id INT NOT NULL, action_task_id INT NOT NULL, code VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AA033145B03A8386 ON ims_action_task_statuses (created_by_id)');
        $this->addSql('CREATE INDEX IDX_AA03314557148967 ON ims_action_task_statuses (action_task_id)');
        $this->addSql('CREATE TABLE ims_action_task_types (id INT NOT NULL, title VARCHAR(255) NOT NULL, handler VARCHAR(255) NOT NULL, action_code VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE ims_categories (id INT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DB04E776727ACA70 ON ims_categories (parent_id)');
        $this->addSql('CREATE TABLE ims_comments (id INT NOT NULL, incident_id INT NOT NULL, action_id INT DEFAULT NULL, created_by_id INT NOT NULL, updated_by_id INT NOT NULL, target_group_id INT NOT NULL, text TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, level VARCHAR(255) NOT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DF47B2B659E53FB9 ON ims_comments (incident_id)');
        $this->addSql('CREATE INDEX IDX_DF47B2B69D32F035 ON ims_comments (action_id)');
        $this->addSql('CREATE INDEX IDX_DF47B2B6B03A8386 ON ims_comments (created_by_id)');
        $this->addSql('CREATE INDEX IDX_DF47B2B6896DBBDE ON ims_comments (updated_by_id)');
        $this->addSql('CREATE INDEX IDX_DF47B2B624FF092E ON ims_comments (target_group_id)');
        $this->addSql('CREATE TABLE ims_incidents (id INT NOT NULL, status_id INT DEFAULT NULL, created_by_id INT NOT NULL, updated_by_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, title TEXT NOT NULL, description TEXT DEFAULT NULL, source JSONB DEFAULT NULL, coverage INT DEFAULT NULL, spread INT DEFAULT NULL, importance INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F68339B6BF700BD ON ims_incidents (status_id)');
        $this->addSql('CREATE INDEX IDX_1F68339BB03A8386 ON ims_incidents (created_by_id)');
        $this->addSql('CREATE INDEX IDX_1F68339B896DBBDE ON ims_incidents (updated_by_id)');
        $this->addSql('CREATE TABLE ims_incident_locations (incident_id INT NOT NULL, location_id INT NOT NULL, PRIMARY KEY(incident_id, location_id))');
        $this->addSql('CREATE INDEX IDX_4BDC935559E53FB9 ON ims_incident_locations (incident_id)');
        $this->addSql('CREATE INDEX IDX_4BDC935564D218E ON ims_incident_locations (location_id)');
        $this->addSql('CREATE TABLE ims_incident_groups (incident_id INT NOT NULL, group_id INT NOT NULL, PRIMARY KEY(incident_id, group_id))');
        $this->addSql('CREATE INDEX IDX_80B875EF59E53FB9 ON ims_incident_groups (incident_id)');
        $this->addSql('CREATE INDEX IDX_80B875EFFE54D947 ON ims_incident_groups (group_id)');
        $this->addSql('CREATE TABLE ims_incident_categories (incident_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY(incident_id, category_id))');
        $this->addSql('CREATE INDEX IDX_A1A835859E53FB9 ON ims_incident_categories (incident_id)');
        $this->addSql('CREATE INDEX IDX_A1A835812469DE2 ON ims_incident_categories (category_id)');
        $this->addSql('CREATE TABLE ims_incident_statuses (id INT NOT NULL, created_by_id INT NOT NULL, incident_id INT NOT NULL, code VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C243F5AAB03A8386 ON ims_incident_statuses (created_by_id)');
        $this->addSql('CREATE INDEX IDX_C243F5AA59E53FB9 ON ims_incident_statuses (incident_id)');
        $this->addSql('CREATE TABLE ims_locations (id INT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, level INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EEDF4CF1727ACA70 ON ims_locations (parent_id)');
        $this->addSql('CREATE TABLE ims_group_permissions (id INT NOT NULL, group_id INT NOT NULL, permission_id INT NOT NULL, restriction JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_79235C66FE54D947 ON ims_group_permissions (group_id)');
        $this->addSql('CREATE INDEX IDX_79235C66FED90CCA ON ims_group_permissions (permission_id)');
        $this->addSql('CREATE UNIQUE INDEX group_permission_unique ON ims_group_permissions (group_id, permission_id)');
        $this->addSql('CREATE TABLE ims_permissions (id INT NOT NULL, code VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, restriction_type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE ims_files ADD CONSTRAINT FK_BD6851EBB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_actions ADD CONSTRAINT FK_B90BA67A6BF700BD FOREIGN KEY (status_id) REFERENCES ims_action_statuses (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_actions ADD CONSTRAINT FK_B90BA67A59E53FB9 FOREIGN KEY (incident_id) REFERENCES ims_incidents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_actions ADD CONSTRAINT FK_B90BA67A80FF6630 FOREIGN KEY (responsible_group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_actions ADD CONSTRAINT FK_B90BA67ABDAD1998 FOREIGN KEY (responsible_user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_actions ADD CONSTRAINT FK_B90BA67AB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_actions ADD CONSTRAINT FK_B90BA67A896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_action_statuses ADD CONSTRAINT FK_41CE9E0CB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_action_statuses ADD CONSTRAINT FK_41CE9E0C9D32F035 FOREIGN KEY (action_id) REFERENCES ims_actions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_action_statuses ADD CONSTRAINT FK_41CE9E0C80FF6630 FOREIGN KEY (responsible_group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_action_statuses ADD CONSTRAINT FK_41CE9E0CBDAD1998 FOREIGN KEY (responsible_user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_action_tasks ADD CONSTRAINT FK_C206E47D9D32F035 FOREIGN KEY (action_id) REFERENCES ims_actions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_action_tasks ADD CONSTRAINT FK_C206E47DC54C8C93 FOREIGN KEY (type_id) REFERENCES ims_action_task_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_action_tasks ADD CONSTRAINT FK_C206E47D6BF700BD FOREIGN KEY (status_id) REFERENCES ims_action_task_statuses (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_action_tasks ADD CONSTRAINT FK_C206E47DB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_action_tasks ADD CONSTRAINT FK_C206E47D896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_action_task_statuses ADD CONSTRAINT FK_AA033145B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_action_task_statuses ADD CONSTRAINT FK_AA03314557148967 FOREIGN KEY (action_task_id) REFERENCES ims_action_tasks (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_categories ADD CONSTRAINT FK_DB04E776727ACA70 FOREIGN KEY (parent_id) REFERENCES ims_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_comments ADD CONSTRAINT FK_DF47B2B659E53FB9 FOREIGN KEY (incident_id) REFERENCES ims_incidents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_comments ADD CONSTRAINT FK_DF47B2B69D32F035 FOREIGN KEY (action_id) REFERENCES ims_actions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_comments ADD CONSTRAINT FK_DF47B2B6B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_comments ADD CONSTRAINT FK_DF47B2B6896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_comments ADD CONSTRAINT FK_DF47B2B624FF092E FOREIGN KEY (target_group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_incidents ADD CONSTRAINT FK_1F68339B6BF700BD FOREIGN KEY (status_id) REFERENCES ims_incident_statuses (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_incidents ADD CONSTRAINT FK_1F68339BB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_incidents ADD CONSTRAINT FK_1F68339B896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_incident_locations ADD CONSTRAINT FK_4BDC935559E53FB9 FOREIGN KEY (incident_id) REFERENCES ims_incidents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_incident_locations ADD CONSTRAINT FK_4BDC935564D218E FOREIGN KEY (location_id) REFERENCES ims_locations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_incident_groups ADD CONSTRAINT FK_80B875EF59E53FB9 FOREIGN KEY (incident_id) REFERENCES ims_incidents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_incident_groups ADD CONSTRAINT FK_80B875EFFE54D947 FOREIGN KEY (group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_incident_categories ADD CONSTRAINT FK_A1A835859E53FB9 FOREIGN KEY (incident_id) REFERENCES ims_incidents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_incident_categories ADD CONSTRAINT FK_A1A835812469DE2 FOREIGN KEY (category_id) REFERENCES ims_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_incident_statuses ADD CONSTRAINT FK_C243F5AAB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_incident_statuses ADD CONSTRAINT FK_C243F5AA59E53FB9 FOREIGN KEY (incident_id) REFERENCES ims_incidents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_locations ADD CONSTRAINT FK_EEDF4CF1727ACA70 FOREIGN KEY (parent_id) REFERENCES ims_locations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_group_permissions ADD CONSTRAINT FK_79235C66FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_group_permissions ADD CONSTRAINT FK_79235C66FED90CCA FOREIGN KEY (permission_id) REFERENCES ims_permissions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ims_incidents ADD repeated_incident_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ims_incidents ADD CONSTRAINT FK_1F68339BB04C3708 FOREIGN KEY (repeated_incident_id) REFERENCES ims_incidents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1F68339BB04C3708 ON ims_incidents (repeated_incident_id)');

    }


    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');



        $this->addSql('ALTER TABLE ims_action_statuses DROP CONSTRAINT FK_41CE9E0C9D32F035');
        $this->addSql('ALTER TABLE ims_action_tasks DROP CONSTRAINT FK_C206E47D9D32F035');
        $this->addSql('ALTER TABLE ims_comments DROP CONSTRAINT FK_DF47B2B69D32F035');
        $this->addSql('ALTER TABLE ims_actions DROP CONSTRAINT FK_B90BA67A6BF700BD');
        $this->addSql('ALTER TABLE ims_action_task_statuses DROP CONSTRAINT FK_AA03314557148967');
        $this->addSql('ALTER TABLE ims_action_tasks DROP CONSTRAINT FK_C206E47D6BF700BD');
        $this->addSql('ALTER TABLE ims_action_tasks DROP CONSTRAINT FK_C206E47DC54C8C93');
        $this->addSql('ALTER TABLE ims_categories DROP CONSTRAINT FK_DB04E776727ACA70');
        $this->addSql('ALTER TABLE ims_incident_categories DROP CONSTRAINT FK_A1A835812469DE2');
        $this->addSql('ALTER TABLE ims_actions DROP CONSTRAINT FK_B90BA67A59E53FB9');
        $this->addSql('ALTER TABLE ims_comments DROP CONSTRAINT FK_DF47B2B659E53FB9');
        $this->addSql('ALTER TABLE ims_incident_locations DROP CONSTRAINT FK_4BDC935559E53FB9');
        $this->addSql('ALTER TABLE ims_incident_groups DROP CONSTRAINT FK_80B875EF59E53FB9');
        $this->addSql('ALTER TABLE ims_incident_categories DROP CONSTRAINT FK_A1A835859E53FB9');
        $this->addSql('ALTER TABLE ims_incident_statuses DROP CONSTRAINT FK_C243F5AA59E53FB9');
        $this->addSql('ALTER TABLE ims_incidents DROP CONSTRAINT FK_1F68339B6BF700BD');
        $this->addSql('ALTER TABLE ims_incident_locations DROP CONSTRAINT FK_4BDC935564D218E');
        $this->addSql('ALTER TABLE ims_locations DROP CONSTRAINT FK_EEDF4CF1727ACA70');
        $this->addSql('ALTER TABLE ims_group_permissions DROP CONSTRAINT FK_79235C66FED90CCA');
        $this->addSql('ALTER TABLE ims_incidents DROP CONSTRAINT FK_1F68339BB04C3708');
        $this->addSql('DROP SEQUENCE ims_files_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ims_actions_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ims_action_statuses_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ims_action_tasks_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ims_action_task_statuses_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ims_action_task_types_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ims_categories_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ims_comments_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ims_incidents_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ims_incident_statuses_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ims_locations_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ims_group_permissions_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ims_permissions_id_seq CASCADE');
        $this->addSql('DROP TABLE ims_files');
        $this->addSql('DROP TABLE ims_actions');
        $this->addSql('DROP TABLE ims_action_statuses');
        $this->addSql('DROP TABLE ims_action_tasks');
        $this->addSql('DROP TABLE ims_action_task_statuses');
        $this->addSql('DROP TABLE ims_action_task_types');
        $this->addSql('DROP TABLE ims_categories');
        $this->addSql('DROP TABLE ims_comments');
        $this->addSql('DROP TABLE ims_incidents');
        $this->addSql('DROP TABLE ims_incident_locations');
        $this->addSql('DROP TABLE ims_incident_groups');
        $this->addSql('DROP TABLE ims_incident_categories');
        $this->addSql('DROP TABLE ims_incident_statuses');
        $this->addSql('DROP TABLE ims_locations');
        $this->addSql('DROP TABLE ims_group_permissions');
        $this->addSql('DROP TABLE ims_permissions');
    }

}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\ApiToken;
use App\Entity\Customer;
use App\Entity\Device;
use App\Entity\DeviceDiagnostic;
use App\Entity\DeviceModel;
use App\Entity\DeviceModelOutput;
use App\Entity\DeviceOutput;
use App\Entity\DeviceStatus;
use App\Entity\EntityDisplayCustomization;
use App\Entity\Network;
use App\Entity\Platform;
use App\Entity\Screen;
use App\Entity\Site;
use App\Entity\Tag;
use App\Entity\TagGroup;
use App\Entity\TagTarget;
use App\Entity\TemplateModel;
use App\Entity\TemplateModelOutput;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\UserRole;
use App\Entity\VideoOverlay;
use App\Entity\VideoStream;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210118000001 extends AbstractMigration
{

    protected function getUserGroupCode($UserGroupNamespace)
    {
        return Uuid::v5($UserGroupNamespace, Device::class)->toBinary();
    }


    public function getDescription(): string
    {
        return 'Digital Signage API 2.0.0';
    }


    public function up(Schema $schema): void
    {


        // Insert UserRole
        $userRoleNamespace = Uuid::fromString(UserRole::UUID_NAMESPACE);
        $userRoleCollectionPost = 'ROLE_%CLASSNAME%_POST_COLLECTION';
        $userRoleCollectionGet = 'ROLE_%CLASSNAME%_GET_COLLECTION';
        $userRoleItemDelete = 'ROLE_%CLASSNAME%_DELETE_ITEM';
        $userRoleItemGet = 'ROLE_%CLASSNAME%_GET_ITEM';
        $userRoleItemPut = 'ROLE_%CLASSNAME%_PUT_ITEM';
        $userRoleItemPatch = 'ROLE_%CLASSNAME%_PATCH_ITEM';
        foreach ([
                     ApiToken::class,
                     Customer::class,
                     Device::class,
                     DeviceDiagnostic::class,
                     DeviceModel::class,
                     DeviceModelOutput::class,
                     DeviceOutput::class,
                     EntityDisplayCustomization::class,
                     Network::class,
                     Platform::class,
                     Screen::class,
                     Site::class,
                     Tag::class,
                     TagGroup::class,
                     TemplateModel::class,
                     TemplateModelOutput::class,
                     User::class,
                     UserGroup::class,
                     UserRole::class,
                     VideoOverlay::class,
                     VideoStream::class,
                 ] as $className) {
            foreach ([
                         [
                             'code' => Uuid::v5($userRoleNamespace,
                                 $this->getFormattedRole($userRoleCollectionGet, $className))->toBinary(),
                             'roleName' => $this->getFormattedRole($userRoleCollectionGet, $className),
                             'name' => $this->getClassname($className) . ' Get List'
                         ],
                         [
                             'code' => Uuid::v5($userRoleNamespace,
                                 $this->getFormattedRole($userRoleCollectionPost, $className))->toBinary(),
                             'roleName' => $this->getFormattedRole($userRoleCollectionPost, $className),
                             'name' => $this->getClassname($className) . ' Create'
                         ],
                         [
                             'code' => Uuid::v5($userRoleNamespace,
                                 $this->getFormattedRole($userRoleItemGet, $className))->toBinary(),
                             'roleName' => $this->getFormattedRole($userRoleItemGet, $className),
                             'name' => $this->getClassname($className) . ' Get Details'
                         ],
                         [
                             'code' => Uuid::v5($userRoleNamespace,
                                 $this->getFormattedRole($userRoleItemPut, $className))->toBinary(),
                             'roleName' => $this->getFormattedRole($userRoleItemPut, $className),
                             'name' => $this->getClassname($className) . ' Update'
                         ],
                         [
                             'code' => Uuid::v5($userRoleNamespace,
                                 $this->getFormattedRole($userRoleItemPatch, $className))->toBinary(),
                             'roleName' => $this->getFormattedRole($userRoleItemPatch, $className),
                             'name' => $this->getClassname($className) . ' Patch'
                         ],
                         [
                             'code' => Uuid::v5($userRoleNamespace,
                                 $this->getFormattedRole($userRoleItemDelete, $className))->toBinary(),
                             'roleName' => $this->getFormattedRole($userRoleItemDelete, $className),
                             'name' => $this->getClassname($className) . ' Delete'
                         ],
                     ] as $userRole) {
                $this->addSql('INSERT INTO `user_role`(`code`,`role_name`,`name`,`created_at`,`updated_at`)VALUES(:code,:roleName,:name,NOW(),NOW());',
                    $userRole);
            }
        }

        // Roles for Read only entities
        foreach ([
                     DeviceStatus::class,
                     TagTarget::class,
                 ] as $className) {
            foreach ([
                         [
                             'code' => Uuid::v5($userRoleNamespace,
                                 $this->getFormattedRole($userRoleCollectionGet, $className))->toBinary(),
                             'roleName' => $this->getFormattedRole($userRoleCollectionGet, $className),
                             'name' => $this->getClassname($className) . 'Get list'
                         ],
                         [
                             'code' => Uuid::v5($userRoleNamespace,
                                 $this->getFormattedRole($userRoleItemGet, $className))->toBinary(),
                             'roleName' => $this->getFormattedRole($userRoleItemGet, $className),
                             'name' => $this->getClassname($className) . 'Get Details'
                         ],
                     ] as $userRole) {
                $this->addSql('INSERT INTO `user_role`(`code`,`role_name`,`name`,`created_at`,`updated_at`)VALUES(:code,:roleName,:name,NOW(),NOW());',
                    $userRole);
            }
        }

        // ROLES for custom patch for Device, DeviceOutput and Site
        foreach ([


                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_API_TOKEN_PATCH_EXPIRED_AT')->toBinary(),
                         'roleName' => 'ROLE_API_TOKEN_PATCH_EXPIRED_AT',
                         'name' => 'ApiToken Patch Expired at'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_CUSTOMER_PATCH_ENABLE')->toBinary(),
                         'roleName' => 'ROLE_CUSTOMER_PATCH_ENABLE',
                         'name' => 'Customer Patch Enable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_CUSTOMER_PATCH_DISABLE')->toBinary(),
                         'roleName' => 'ROLE_CUSTOMER_PATCH_DISABLE',
                         'name' => 'Customer Patch Disable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_GET_ENABLED_OUTPUTS')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_GET_ENABLED_OUTPUTS',
                         'name' => 'Device Get Enabled Outputs'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_CUSTOMER_PATCH_NAME')->toBinary(),
                         'roleName' => 'ROLE_CUSTOMER_PATCH_NAME',
                         'name' => 'Customer Patch Name and Description'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_PATCH_ENABLE')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_PATCH_ENABLE',
                         'name' => 'Device Patch Enable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_PATCH_DISABLE')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_PATCH_DISABLE',
                         'name' => 'Device Patch Disable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_PATCH_COMMENT')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_PATCH_COMMENT',
                         'name' => 'Device Patch Comment'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_PATCH_INTERNAL_COMMENT')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_PATCH_INTERNAL_COMMENT',
                         'name' => 'Device Patch Internal Comment'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_PATCH_NAME')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_PATCH_NAME',
                         'name' => 'Device Patch Name and Description'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_PATCH_NETWORK')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_PATCH_NETWORK',
                         'name' => 'Device Patch Network'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_PATCH_PLATFORM')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_PATCH_PLATFORM',
                         'name' => 'Device Patch Platform'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_PATCH_SITE')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_PATCH_SITE',
                         'name' => 'Device Patch Site'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_PATCH_STATUS')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_PATCH_STATUS',
                         'name' => 'Device Patch Status'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_PATCH_REMOVE_GROUP')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_PATCH_REMOVE_GROUP',
                         'name' => 'Device Patch Remove Group'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_OUTPUT_PATCH_ENABLE')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_OUTPUT_PATCH_ENABLE',
                         'name' => 'DeviceOutput Patch Enable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_OUTPUT_PATCH_DISABLE')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_OUTPUT_PATCH_DISABLE',
                         'name' => 'DeviceOutput Patch Disable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_OUTPUT_PATCH_VIDEO_STREAM')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_OUTPUT_PATCH_VIDEO_STREAM',
                         'name' => 'DeviceOutput Patch Video Stream'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_DEVICE_OUTPUT_PATCH_VIDEO_OVERLAY')->toBinary(),
                         'roleName' => 'ROLE_DEVICE_OUTPUT_PATCH_VIDEO_OVERLAY',
                         'name' => 'DeviceOutput Patch Video Overlay'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace,
                             'ROLE_ENTITY_DISPLAY_CUSTOMIZATION_PATCH_CUSTOM')->toBinary(),
                         'roleName' => 'ROLE_ENTITY_DISPLAY_CUSTOMIZATION_PATCH_CUSTOM',
                         'name' => 'EntityDisplayCustomization Patch Custom'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace,
                             'ROLE_ENTITY_DISPLAY_CUSTOMIZATION_POST_CUSTOM')->toBinary(),
                         'roleName' => 'ROLE_ENTITY_DISPLAY_CUSTOMIZATION_POST_CUSTOM',
                         'name' => 'EntityDisplayCustomization Create Custom'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace,
                             'ROLE_ENTITY_DISPLAY_CUSTOMIZATION_PUT_CUSTOM')->toBinary(),
                         'roleName' => 'ROLE_ENTITY_DISPLAY_CUSTOMIZATION_PUT_CUSTOM',
                         'name' => 'EntityDisplayCustomization Update Custom'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_NETWORK_PATCH_ENABLE')->toBinary(),
                         'roleName' => 'ROLE_NETWORK_PATCH_ENABLE',
                         'name' => 'Network Patch Enable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_NETWORK_PATCH_DISABLE')->toBinary(),
                         'roleName' => 'ROLE_NETWORK_PATCH_DISABLE',
                         'name' => 'Network Patch Disable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_NETWORK_PATCH_NAME')->toBinary(),
                         'roleName' => 'ROLE_NETWORK_PATCH_NAME',
                         'name' => 'Network Patch Name and Description'
                     ],

                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_PLATFORM_PATCH_ENABLE')->toBinary(),
                         'roleName' => 'ROLE_PLATFORM_PATCH_ENABLE',
                         'name' => 'Platform Patch Enable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_PLATFORM_PATCH_DISABLE')->toBinary(),
                         'roleName' => 'ROLE_PLATFORM_PATCH_DISABLE',
                         'name' => 'Platform Patch Disable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_PLATFORM_PATCH_NAME')->toBinary(),
                         'roleName' => 'ROLE_PLATFORM_PATCH_NAME',
                         'name' => 'Platform Patch Name and Description'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_SCREEN_PATCH_ENABLE')->toBinary(),
                         'roleName' => 'ROLE_SCREEN_PATCH_ENABLE',
                         'name' => 'Screen Patch Enable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_SCREEN_PATCH_DISABLE')->toBinary(),
                         'roleName' => 'ROLE_SCREEN_PATCH_DISABLE',
                         'name' => 'Screen Patch Disable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_SCREEN_PATCH_NAME')->toBinary(),
                         'roleName' => 'ROLE_SCREEN_PATCH_NAME',
                         'name' => 'Screen Patch Name and Description'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_SITE_PATCH_ENABLE')->toBinary(),
                         'roleName' => 'ROLE_SITE_PATCH_ENABLE',
                         'name' => 'Site Patch Enable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_SITE_PATCH_DISABLE')->toBinary(),
                         'roleName' => 'ROLE_SITE_PATCH_DISABLE',
                         'name' => 'Site Patch Disable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_SITE_PATCH_NAME')->toBinary(),
                         'roleName' => 'ROLE_SITE_PATCH_NAME',
                         'name' => 'Site Patch Name and Description'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_VIDEO_OVERLAY_PATCH_ENABLE')->toBinary(),
                         'roleName' => 'ROLE_VIDEO_OVERLAY_PATCH_ENABLE',
                         'name' => 'VideoOverlay Patch Enable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_VIDEO_OVERLAY_PATCH_DISABLE')->toBinary(),
                         'roleName' => 'ROLE_VIDEO_OVERLAY_PATCH_DISABLE',
                         'name' => 'VideoOverlay Patch Disable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_VIDEO_OVERLAY_PATCH_NAME')->toBinary(),
                         'roleName' => 'ROLE_VIDEO_OVERLAY_PATCH_NAME',
                         'name' => 'VideoOverlay Patch Name'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_VIDEO_STREAM_PATCH_ENABLE')->toBinary(),
                         'roleName' => 'ROLE_VIDEO_STREAM_PATCH_ENABLE',
                         'name' => 'VideoStream Patch Enable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_VIDEO_STREAM_PATCH_DISABLE')->toBinary(),
                         'roleName' => 'ROLE_VIDEO_STREAM_PATCH_DISABLE',
                         'name' => 'VideoStream Patch Disable'
                     ],
                     [
                         'code' => Uuid::v5($userRoleNamespace, 'ROLE_VIDEO_STREAM_PATCH_NAME')->toBinary(),
                         'roleName' => 'ROLE_VIDEO_STREAM_PATCH_NAME',
                         'name' => 'Videostream Patch Name'
                     ],


                 ] as $patchCustomRoles) {

            $this->addSql('INSERT INTO `user_role`(`code`,`role_name`,`name`,`created_at`,`updated_at`)VALUES(:code,:roleName,:name,NOW(),NOW());',
                $patchCustomRoles);
        }


        // Insert DeviceStatus
        $deviceStatusNamespace = Uuid::fromString(DeviceStatus::UUID_NAMESPACE);
        foreach ([
                     [
                         'code' => Uuid::v5($deviceStatusNamespace, 'ordered')->toBinary(),
                         'name' => 'Ordered',
                         'description' => ''
                     ],
                     [
                         'code' => Uuid::v5($deviceStatusNamespace, 'stored')->toBinary(),
                         'name' => 'Stored',
                         'description' => ''
                     ],
                     [
                         'code' => Uuid::v5($deviceStatusNamespace, 'delivered')->toBinary(),
                         'name' => 'Delivered',
                         'description' => ''
                     ],
                     [
                         'code' => Uuid::v5($deviceStatusNamespace, 'installed')->toBinary(),
                         'name' => 'Installed',
                         'description' => ''
                     ],
                     [
                         'code' => Uuid::v5($deviceStatusNamespace, 'broken')->toBinary(),
                         'name' => 'Broken',
                         'description' => ''
                     ],
                     [
                         'code' => Uuid::v5($deviceStatusNamespace, 'removed')->toBinary(),
                         'name' => 'Removed',
                         'description' => ''
                     ],
                     [
                         'code' => Uuid::v5($deviceStatusNamespace, 'sent_to_after_sale')->toBinary(),
                         'name' => 'Sent to after sale',
                         'description' => ''
                     ],
                     [
                         'code' => Uuid::v5($deviceStatusNamespace, 'repaired')->toBinary(),
                         'name' => 'Repaired',
                         'description' => ''
                     ],
                 ] as $deviceStatus) {
            $this->addSql('INSERT INTO `device_status`(`code`,`name`,`description`,`created_at`,`updated_at`)VALUES(:code,:name,:description,NOW(),NOW());',
                $deviceStatus);
        }

        // Insert TagTarget
        $tagTargetNamespace = Uuid::fromString(TagTarget::UUID_NAMESPACE);
        foreach ([
                     [
                         'code' => Uuid::v5($tagTargetNamespace, Device::class)->toBinary(),
                         'name' => 'Device',
                         'description' => '',
                         'entityClassName' => Device::class
                     ],
                     [
                         'code' => Uuid::v5($tagTargetNamespace, Network::class)->toBinary(),
                         'name' => 'Network',
                         'description' => '',
                         'entityClassName' => Network::class
                     ],
                     [
                         'code' => Uuid::v5($tagTargetNamespace, Site::class)->toBinary(),
                         'name' => 'Site',
                         'description' => '',
                         'entityClassName' => Site::class
                     ],
                     [
                         'code' => Uuid::v5($tagTargetNamespace, Screen::class)->toBinary(),
                         'name' => 'Screen',
                         'description' => '',
                         'entityClassName' => Screen::class
                     ],
                     [
                         'code' => Uuid::v5($tagTargetNamespace, VideoOverlay::class)->toBinary(),
                         'name' => 'VideoOverlay',
                         'description' => '',
                         'entityClassName' => VideoOverlay::class
                     ],
                     [
                         'code' => Uuid::v5($tagTargetNamespace, VideoStream::class)->toBinary(),
                         'name' => 'VideoStream',
                         'description' => '',
                         'entityClassName' => VideoStream::class
                     ],
                 ] as $tagTarget) {
            $this->addSql('INSERT INTO `tag_target`(`code`,`name`,`description`,`entity_class_name`,`created_at`,`updated_at`)VALUES(:code,:name,:description,:entityClassName,NOW(),NOW());',
                $tagTarget);
        }


    }

    public function postUp(Schema $schema): void
    {
    }

    public function down(Schema $schema): void
    {

        // this down() migration is auto-generated, please modify it to your needs

    }

    protected function getClassname(string $fullClassname): string
    {
        return substr($fullClassname, strrpos($fullClassname, '\\') + 1);
    }

    protected function getFormattedRole(string $roleTemplate, string $fullClassname): string
    {

        //Keep only classname without namespace
        $classname = substr($fullClassname, strrpos($fullClassname, '\\') + 1);

        //from CamelCase to SCREAMING_SNAKE_CASE
        $upperClassname = strtoupper(join('_', preg_split('/(?=[A-Z])/', $classname, 0, PREG_SPLIT_NO_EMPTY)));

        return str_replace('%CLASSNAME%', $upperClassname, $roleTemplate);
    }
}

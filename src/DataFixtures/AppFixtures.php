<?php

namespace App\DataFixtures;

use App\Entity\UserRole;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Symfony\Component\Uid\Uuid;
use App\Entity\User;
use App\Entity\Customer;
use App\Entity\DeviceStatus;
use App\Entity\TagTarget;
use App\Entity\UserGroup;
use App\Entity\DeviceModel;
use App\Entity\Network;
use App\Entity\Platform;
use App\Entity\TemplateModel;
use App\Entity\Site;
use App\Entity\Embeddable\Contact;
use App\Entity\Embeddable\Address;
use App\Entity\Device;
use App\Entity\DeviceDiagnostic;
use App\Entity\DeviceModelOutput;
use App\Entity\VideoStream;
use App\Entity\VideoOverlay;
use App\Entity\TemplateModelOutput;
use App\Entity\DeviceOutput;
use App\Entity\Screen;
use App\Entity\TagGroup;
use App\Entity\Tag;
use App\Security\TokenGenerator;
use App\Entity\ApiToken;
use DateTimeInterface;


class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var \Faker\Factory
     */
    private $faker;

    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;

    private const USERS = [
        [

            'username' => 'admin@blog.com',
            'email' => 'admin@blog.com',
            'name' => 'Piotr Jura',
            'password' => 'secret123#',
            'roles' => ['ROLE_SUPER_ADMIN'],
        ],
        [
            'username' => 'john@blog.com',
            'email' => 'john@blog.com',
            'name' => 'John Doe',
            'password' => 'secret123#',
            'roles' => ['ROLE_USER'],

        ],
        [
            'username' => 'rob@blog.com',
            'email' => 'rob@blog.com',
            'name' => 'Rob Smith',
            'password' => 'secret123#',
            'roles' => ['ROLE_ALLOWED_TO_SWITCH'],

        ],
        [
            'username' => 'jenny@blog.com',
            'email' => 'jenny@blog.com',
            'name' => 'Jenny Rowling',
            'password' => 'secret123#',
            'roles' => ['ROLE_ALLOWED_TO_SWITCH'],

        ],
    ];
    private const ROLES = ['CAN_SWITCH_USER','ROLE_SUPER_ADMIN','ROLE_SUPER_ADMIN'];

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, TokenGenerator $tokenGenerator)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = \Faker\Factory::create();
        $this->tokenGenerator = $tokenGenerator;
    }

    public function load(ObjectManager $manager)
    {
        $this->LoadUserRoles($manager);
        $this->loadUsers($manager);
        $this->loadCustomers($manager);
        $this->LoadDevicestatuses($manager);
        $this->LoadTagTarget($manager);
        $this->LoadUserGroup($manager);
        $this->LoadDeviceModel($manager);
        $this->LoadNetwork($manager);
        $this->LoadPlatform($manager);
        $this->LoadTemplateModel($manager);
        $this->LoadSite($manager);
        $this->LoadDevice($manager);
        //$this->LoadDeviceDiagnostic($manager);
        $this->LoadDeviceModelOutput($manager);
        $this->LoadVideoStream($manager);
        $this->LoadVideoOverlay($manager);
        $this->LoadTemplateModelOutput($manager);
        $this->LoadDeviceOutput($manager);
        $this->LoadScreen($manager);
        $this->LoadTagGroup($manager);
        $this->LoadTag($manager);
        $this->LoadApiToken($manager);

    }

    public function loadUsers(ObjectManager $manager)
    {
        foreach (self::USERS as $userFixture) {
            $user = new User();
            $uuid = Uuid::v1();
            $user->setUsername($userFixture['username']);
            $user->setCode($uuid);
            $user->setEmail($userFixture['email']);
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $userFixture['password']
                )
            );
            $user->setIsSuperAdmin(true);
            $user->setActivatedToken(
                $this->tokenGenerator->getRandomSecureToken()
            );
            $user->setActivatedTokenAt(new DateTime());
            $this->addReference('user_' . $userFixture['username'], $user);
            $i = rand(0, 2);
            //$this->addUserRole($this->getReference("userRole_$i"));

            $manager->persist($user);
        }
        $manager->flush();

    }

    public function loadCustomers(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $customer = new Customer();
                $customer->setCode($uuid);
                $customer->setName($this->faker->name());
                $customer->setDescription($this->faker->realText(rand(10, 20)));
                $this->setReference("customer_$i", $customer);
                $manager->persist($customer);
            }
        }

        $manager->flush();
    }

    public function LoadDevicestatuses(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $deviceStatus = new DeviceStatus();
                $deviceStatus->setCode($uuid);
                $deviceStatus->setName($this->faker->name());
                $deviceStatus->setDescription($this->faker->realText(rand(10, 20)));
                $this->setReference("deviceStatus_$i", $deviceStatus);
                $manager->persist($deviceStatus);
            }
        }

        $manager->flush();
    }

    public function LoadTagTarget(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $tagTarget = new TagTarget();
                $tagTarget->setCode($uuid);
                $tagTarget->setName($this->faker->lastName());
                $tagTarget->setDescription($this->faker->realText(rand(10, 20)));
                $tagTarget->setEntityClassName($this->faker->realText(rand(20, 50)));
                $this->setReference("tagTarget_$i", $tagTarget);

                $manager->persist($tagTarget);
            }
        }

        $manager->flush();
    }

    public function LoadUserGroup(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $userGroup = new UserGroup();
                $userGroup->setCustomer($this->getReference("customer_$i"));
                $userGroup->setCode($uuid);
                $userGroup->setName($this->faker->lastName());
                $userGroup->setDescription($this->faker->realText(rand(10, 20)));
                $this->setReference("user_group_$i", $userGroup);
                $manager->persist($userGroup);
            }
        }

        $manager->flush();
    }

    public function LoadDeviceModel(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $deviceModel = new DeviceModel();
                $deviceModel->setCode($uuid);
                $deviceModel->setName($this->faker->name());
                $deviceModel->setDescription($this->faker->realText(rand(10, 20)));
                $this->setReference("deviceModel_$i", $deviceModel);

                $manager->persist($deviceModel);
            }
        }

        $manager->flush();
    }

    public function LoadNetwork(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $network = new Network();
                $network->setCode($uuid);
                $network->setName($this->faker->name());
                $network->setDescription($this->faker->realText(rand(10, 20)));
                $network->setUserGroup($this->getReference("user_group_$i"));
                $network->setPublicIpV4($this->faker->localIpv4);
                $network->setPublicIpV6($this->faker->ipv6);
                $network->setComment($this->faker->realText(rand(10, 20)));
                $this->setReference("network_$i", $network);

                $manager->persist($network);
            }
        }

        $manager->flush();
    }

    public function LoadPlatform(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $platform = new Platform();
                $platform->setUserGroup($this->getReference("user_group_$i"));
                $platform->setCode($uuid);
                $platform->setName($this->faker->name());
                $platform->setDescription($this->faker->realText(rand(10, 20)));
                $platform->setApiHttpUrl($this->faker->url);
                $platform->setApiHttpProxy($this->faker->url);
                $platform->setApiSocketUrl($this->faker->url);
                $platform->setColor(substr($this->faker->hexcolor, 1));
                $this->setReference("platform_$i", $platform);

                $manager->persist($platform);
            }
        }

        $manager->flush();
    }

    public function LoadTemplateModel(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $templateModel = new TemplateModel();
                $templateModel->setPlatform($this->getReference("platform_$i"));
                $templateModel->setCode($uuid);
                $templateModel->setName($this->faker->name());
                $this->setReference("templateModel_$i", $templateModel);

                $manager->persist($templateModel);
            }
        }

        $manager->flush();
    }

    public function LoadSite(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();

                $site = new Site();
                $site->setUserGroup($this->getReference("user_group_$i"));
                $site->setTemplateModel($this->getReference("templateModel_$i"));
                $site->setCode($uuid);
                $site->setName($this->faker->name());
                $site->setDescription($this->faker->realText(rand(10, 20)));

                $contact = new Contact();
                $contact->setGender($this->faker->randomElement(['M', 'F']));
                $contact->setFirstName($this->faker->firstName);
                $contact->setLastName($this->faker->lastName);
                $contact->setEmail($this->faker->email);
                $contact->setPhone($this->faker->e164PhoneNumber);
                $site->setContact($contact);

                $address = new Address();
                $address->setCountry($this->faker->countryCode);
                $address->setCity($this->faker->city);
                $address->setZipCode($this->faker->postcode);
                $address->setStreet($this->faker->streetAddress);
                $site->setAddress($address);

                $this->setReference("site_$i", $site);
                $manager->persist($site);
            }
        }

        $manager->flush();
    }

    public function LoadDevice(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $device = new Device();
                $device->setUserGroup($this->getReference("user_group_$i"));
                $device->setModel($this->getReference("deviceModel_$i"));
                $device->setSite($this->getReference("site_$i"));
                $device->setNetwork($this->getReference("network_$i"));
                $device->setPlatform($this->getReference("platform_$i"));
                $device->setStatus($this->getReference("deviceStatus_$i"));
                $device->setCode($uuid);
                $device->setName($this->faker->name());
                $device->setDescription($this->faker->realText(rand(10, 20)));
                $device->setEnabled($this->faker->randomElement([true, false]));
                $device->setComment($this->faker->realText(rand(20, 40)));
                $device->setInternalComment($this->faker->realText(rand(20, 40)));
                $device->setSerialNumber($this->faker->randomNumber($nbDigits = NULL, $strict = false));
                $device->setMacAddress($this->faker->macAddress);
                $device->setWantedOsVersion($this->faker->randomElement(['X 10.8', 'X 10.7', 'X 10.6', 'X 10.5']));
                $device->setOsVersion($this->faker->randomElement(['X 10.8', 'X 10.7', 'X 10.6', 'X 10.5']));
                $device->setSoftwareVersion($this->faker->randomElement(['10.8', '10.7', '10.6', '10.5']));
                $device->setWantedSoftwareVersion($this->faker->randomElement(['10.8', '10.7', '10.6', '10.5']));
                $device->setIsSshEnabled($this->faker->randomElement([0, 1]));
                $device->setIsVpnEnabled($this->faker->randomElement([0, 1]));
                $this->setReference("device_$i", $device);


                $manager->persist($device);
            }
        }

        $manager->flush();
    }

    public function LoadDeviceDiagnostic(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            $uuid = Uuid::v1();
            $deviceDiagnostic = new DeviceDiagnostic();
            $deviceDiagnostic->setDevice($this->getReference("device_$i"));
            $deviceDiagnostic->setCode($uuid);
            $deviceDiagnostic->setIpV4($this->faker->localIpv4);
            $deviceDiagnostic->setIpV6($this->faker->ipv6);
            $deviceDiagnostic->setLastPingAt($this->faker->dateTime($max = 'now', $timezone = null));
            $deviceDiagnostic->setLastHttpConnectionAt($this->faker->dateTime($max = 'now', $timezone = null));
            $deviceDiagnostic->setLastSshConnectionAt($this->faker->dateTime($max = 'now', $timezone = null));
            $deviceDiagnostic->setLastVpnConnectionAt($this->faker->dateTime($max = 'now', $timezone = null));
            $deviceDiagnostic->setLastWebsocketConnectionAt($this->faker->dateTime($max = 'now', $timezone = null));
            $manager->persist($deviceDiagnostic);

        }

        $manager->flush();
    }


    public function LoadDeviceModelOutput(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            $uuid = Uuid::v1();
            $deviceModelOutput = new DeviceModelOutput();
            $deviceModelOutput->setModel($this->getReference("deviceModel_$i"));
            $deviceModelOutput->setCode($uuid);
            $deviceModelOutput->setName($this->faker->name());
            $deviceModelOutput->setDescription($this->faker->realText(rand(10, 20)));
            $deviceModelOutput->setNumber($this->faker->numberBetween($min = 0, $max = 1000));
            $this->setReference("deviceModelOutput_$i", $deviceModelOutput);
            $manager->persist($deviceModelOutput);

        }

        $manager->flush();
    }


    public function LoadVideoStream(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $videoStream = new VideoStream();
                $videoStream->setUserGroup($this->getReference("user_group_$i"));
                $videoStream->setCode($uuid);
                $videoStream->setName($this->faker->name());
                $videoStream->setUrl($this->faker->url);
                $this->setReference("videoStream_$i", $videoStream);

                $manager->persist($videoStream);
            }
        }

        $manager->flush();
    }


    public function LoadVideoOverlay(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $videoOverlay = new VideoOverlay();
                $videoOverlay->setUserGroup($this->getReference("user_group_$i"));
                $videoOverlay->setCode($uuid);
                $videoOverlay->setName($this->faker->name());
                $videoOverlay->setUrl($this->faker->url);
                $this->setReference("videoOverlay_$i", $videoOverlay);
                $manager->persist($videoOverlay);
            }
        }

        $manager->flush();
    }

    public function LoadTemplateModelOutput(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $templateModelOutput = new TemplateModelOutput();
                $templateModelOutput->setTemplateModel($this->getReference("templateModel_$i"));
                $templateModelOutput->setVideoStream($this->getReference("videoStream_$i"));
                $templateModelOutput->setVideoOverlay($this->getReference("videoOverlay_$i"));
                $templateModelOutput->setCode($uuid);
                $templateModelOutput->setNumber($this->faker->numberBetween($min = 0, $max = 1000));
                $this->setReference("templateModelOutput_$i", $templateModelOutput);

                $manager->persist($templateModelOutput);
            }
        }

        $manager->flush();
    }

    public function LoadDeviceOutput(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $deviceOutput = new DeviceOutput();
                $deviceOutput->setDevice($this->getReference("device_$i"));
                $deviceOutput->setModelOutput($this->getReference("deviceModelOutput_$i"));
                $deviceOutput->setVideoStream($this->getReference("videoStream_$i"));
                $deviceOutput->setVideoOverlay($this->getReference("videoOverlay_$i"));
                $deviceOutput->setTemplateModelOutput($this->getReference("templateModelOutput_$i"));
                $deviceOutput->setCode($uuid);
                $deviceOutput->setEnabled($this->faker->randomElement([true, false]));
                $this->setReference("deviceOutput_$i", $deviceOutput);

                $manager->persist($deviceOutput);
            }
        }

        $manager->flush();
    }

    public function LoadScreen(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            $uuid = Uuid::v1();
            $screen = new Screen();
            $screen->setDeviceOutput($this->getReference("deviceOutput_$i"));
            $screen->setSite($this->getReference("site_$i"));
            $screen->setCode($uuid);
            $screen->setName($this->faker->name());
            $screen->setDescription($this->faker->realText(rand(10, 20)));

            $manager->persist($screen);

        }

        $manager->flush();
    }

    public function LoadTagGroup(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $tagGroup = new TagGroup();
                $tagGroup->setUserGroup($this->getReference("user_group_$i"));
                $tagGroup->setTarget($this->getReference("tagTarget_$i"));
                $tagGroup->setCode($uuid);
                $tagGroup->setName($this->faker->name());
                $tagGroup->setDescription($this->faker->realText(rand(10, 20)));
                $this->setReference("tagGroup_$i", $tagGroup);

                $manager->persist($tagGroup);
            }

        }

        $manager->flush();
    }


    public function LoadTag(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $uuid = Uuid::v1();
                $tag = new Tag();
                $tag->setTagGroup($this->getReference("tagGroup_$i"));

                $tag->setCode($uuid);
                $tag->setName($this->faker->name());
                $tag->setDescription($this->faker->realText(rand(10, 20)));

                $manager->persist($tag);
            }

        }

        $manager->flush();
    }

    public function LoadApiToken(ObjectManager $manager)
    {
        foreach (self::USERS as $userFixture) {
            for ($i = 0; $i < 5; $i++) {
                $token = $this->tokenGenerator->getRandomSecureToken();
                $apiToken = new ApiToken();
                $uuid = Uuid::v1();
                $apiToken->setCode($uuid);
                $apiToken->setToken($token);
                $userF = $userFixture['username'];
                $apiToken->setUser($this->getReference("user_$userF"));
                $apiToken->setUserGroup($this->getReference("user_group_$i"));
                $date = new \DateTime('@'.strtotime('+1 day'));
                $apiToken->setExpiredAt($date);
                $apiToken->setName($this->faker->name());
                $manager->persist($apiToken);
            }
        }

        $manager->flush();
    }

    public function LoadUserRoles(ObjectManager $manager)
    {
       $i=0;
        foreach (self::ROLES as $roleFixture) {

                $userRole = new UserRole();
                $uuid = Uuid::v1();
                $userRole->setCode($uuid);
                $userRole->setName($this->faker->name());
                $userRole->setDescription($this->faker->realText(rand(10, 20)));
                $userRole->setName($roleFixture);
                $userRole->setRoleName($roleFixture);
                $this->setReference('userRole_' . $i, $userRole);
                $i++;
                $manager->persist($userRole);
        }

        $manager->flush();
    }
}

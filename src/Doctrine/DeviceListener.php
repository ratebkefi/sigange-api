<?php


namespace App\Doctrine;


use App\Entity\Device;
use App\Entity\DeviceOutput;
use Symfony\Component\Uid\Uuid;

class DeviceListener
{
    public function prePersist(Device $device)
    {


        //TODO if model change has to delete previous outputs
        // if model doesn't change do nothing
        // on create Device create all output from model

        if ($device && $device->getModel()) {
            foreach ($device->getModel()->getOutputs() as $modelOutput) {
                $deviceOutput = (new DeviceOutput())
                    ->setCode(Uuid::v4())
                    ->setEnabled(true)
                    ->setModelOutput($modelOutput);

                $device->addOutput($deviceOutput);
            }
        }
    }
}

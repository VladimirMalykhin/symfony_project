<?php

namespace App\Entity;

use App\Repository\UserStatisticGroupRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserStatisticGroupRepository::class)
 */
class UserStatisticGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=StatisticGroup::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $statisticGroup;


    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }


    public function getStatisticGroup(): ?StatisticGroup
    {
        return $this->statisticGroup;
    }

    public function setStatisticGroup(?StatisticGroup $group): self
    {
        $this->statisticGroup = $group;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}

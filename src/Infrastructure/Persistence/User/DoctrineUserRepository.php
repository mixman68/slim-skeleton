<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class DoctrineUserRepository implements UserRepository
{
    /**
     * @var User[]
     */
    private array $users;

    private EntityRepository $ormRepository;

    /**
     * @param User[]|null $users
     */
    public function __construct(EntityManager $em)
    {
        $this->ormRepository = $em->getRepository(User::class);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return $this->ormRepository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findUserOfId(int $id): User
    {
        $user = $this->ormRepository->find($id);

        if ($user == null) {
            throw new UserNotFoundException();
        }

        return $user;
    }
}

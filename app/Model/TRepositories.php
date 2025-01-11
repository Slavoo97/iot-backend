<?php declare(strict_types = 1);

namespace App\Model;

use App\Model\Services\PostRepository;
use App\Model\Entity\Post;

/**
 * @mixin EntityManager
 */
trait TRepositories
{

	public function getPostRepository(): PostRepository
	{
		return $this->getRepository(Post::class);
	}

}

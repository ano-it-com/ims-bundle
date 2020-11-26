<?php

namespace ANOITCOM\IMSBundle\Services\Incident;

use ANOITCOM\IMSBundle\Repository\Incident\Category\CategoryRepository;

class CategoriesService
{

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;


    public function __construct(CategoryRepository $categoryRepository)
    {

        $this->categoryRepository = $categoryRepository;
    }


    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param null       $limit
     * @param null       $offset
     *
     * @return []
     */
    public function findByAsOptions(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        $entities = $this->categoryRepository->findBy($criteria, $orderBy, $limit, $offset);

        $tree = [];

        foreach ($entities as $entity) {
            $id     = $entity->getId();
            $parent = $entity->getParent();

            $rootId = $parent ? $parent->getId() : $id;

            if ( ! isset($tree[$rootId])) {
                $tree[$rootId] = [
                    'id'       => $rootId,
                    'children' => [],
                ];
            }

            if ( ! $parent) {
                $tree[$id]['title']       = $entity->getTitle();
                $tree[$id]['description'] = $entity->getDescription();
            } else {
                $tree[$parent->getId()]['children'][] = [
                    'id'          => $entity->getId(),
                    'title'       => $entity->getTitle(),
                    'description' => $entity->getDescription()
                ];
            }

        }

        return array_values($tree);
    }
}
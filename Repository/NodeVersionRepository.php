<?php

namespace Kunstmaan\AdminNodeBundle\Repository;

use Kunstmaan\AdminNodeBundle\Entity\HasNodeInterface;
use Kunstmaan\AdminNodeBundle\Entity\Node;
use Kunstmaan\AdminNodeBundle\Entity\NodeTranslation;
use Kunstmaan\AdminNodeBundle\Entity\NodeVersion;
use Kunstmaan\AdminBundle\Modules\ClassLookup;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Kunstmaan\AdminBundle\Entity\AddCommand;

/**
 * NodeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NodeVersionRepository extends EntityRepository
{
	public function getNodeVersionFor(HasNodeInterface $hasNode) {
		return $this->findOneBy(array('refId' => $hasNode->getId(), 'refEntityname' => ClassLookup::getClass($hasNode)));
	}

	public function getNodeForSlug($parentNode, $slug){
		$slugparts = explode("/", $slug);
		$result = null;
		foreach($slugparts as $slugpart){
			if($parentNode){
				//$result = $this->findOneBy(array('slug' => $slugpart, 'parent' => $parentNode->getId())) or $result;
				if($r = $this->findOneBy(array('slug' => $slugpart, 'parent' => $parentNode->getId()))){
					$result = $r;
				}
			} else {
				//$result = $this->findOneBy(array('slug' => $slugpart)) or $result;
				if($r = $this->findOneBy(array('slug' => $slugpart))){
					$result = $r;
				}
			}
		}
		return $result;
	}

	public function createNodeVersionFor(HasNodeInterface $hasNode, NodeTranslation $nodeTranslation, $owner, $type = "public"){
		$em = $this->getEntityManager();
		$classname = ClassLookup::getClass($hasNode);
		if(!$hasNode->getId()>0){
			throw new \Exception("the entity of class ". $classname . " has no id, maybe you forgot to flush first");
		}
		$entityrepo = $em->getRepository($classname);
		$nodeVersion = new NodeVersion();
		$nodeVersion->setNodeTranslation($nodeTranslation);
		$nodeVersion->setType($type);
		$nodeVersion->setVersion($nodeTranslation->getNodeVersions()->count()+1);
		$nodeVersion->setOwner($owner);
		$nodeVersion->setRefId($hasNode->getId());
		$nodeVersion->setRefEntityname($classname);

		$addcommand = new AddCommand($em, $owner);
		$addcommand->execute("new version for page \"". $nodeTranslation->getTitle() ."\" with locale: " . $nodeTranslation->getLang(), array('entity'=> $nodeVersion));

		$em->refresh($nodeVersion);

		return $nodeVersion;
	}

	public function getFor(Node $node, $lang, $user, $permission, $includehiddenfromnav = false)
	{
	    $qb = $this->createQueryBuilder('t')
	    ->select('t')
	    ->innerJoin("t.node", "b")
	    ->where('b.deleted = 0')
	    ->andWhere("t.node = :node")->setParameter("node", $node->getId());

	    if (!$includehiddenfromnav) {
	        $qb->andWhere('b.hiddenfromnav != true');
	    }

	    $qb->andWhere('t.id IN (
	            SELECT p.refId FROM Kunstmaan\AdminBundle\Entity\Permission p WHERE p.refEntityname = ?1 AND p.permissions LIKE ?2 AND p.refGroup IN(?3)
	    )')
	    ->andWhere("t.lang = :lang");

	    $qb->addOrderBy('t.weight', 'ASC')
	    ->addOrderBy('t.title', 'ASC')
	    ->setParameter(1, 'Kunstmaan\\AdminNodeBundle\\Entity\\Node')
	    ->setParameter(2, '%|'.$permission.':1|%');

	    $groupIds = $user->getGroupIds();
	    if (!empty($groupIds)) {
	        $qb->setParameter(3, $groupIds);
	    } else {
	        $qb->setParameter(3, null);
	    }
	    $qb->setParameter("lang", $lang);
	    $query = $qb->getQuery();
	    $query->useResultCache(true, 3600);
	    $result = $query->getOneOrNullResult();

	    return $result;
	}
}
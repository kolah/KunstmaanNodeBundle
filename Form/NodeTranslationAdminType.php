<?php

namespace Kunstmaan\NodeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * NodeTranslationAdminType
 */
class NodeTranslationAdminType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        //$builder->add('node', 'entity_id', array('class' => 'Kunstmaan\NodeBundle\Entity\Node'));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'nodetranslation';
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Kunstmaan\NodeBundle\Entity\NodeTranslation',
        );
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Ajitesh
 * Date: 6/16/14
 * Time: 11:06 AM
 */
namespace Acme\MyDriveBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class WikiDocType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('docName','text');
        $builder->add('docNo','text',array('required'=>false));
        $builder->add('content', 'textarea',array('required'=>false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Acme\MyDriveBundle\Document\WikiDoc',
        ));
    }

    public function getName()
    {
        return 'wikiDoc';
    }
}
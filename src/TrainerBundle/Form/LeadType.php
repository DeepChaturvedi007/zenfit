<?php

namespace TrainerBundle\Form;

use AppBundle\Entity\Lead;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class LeadType extends AbstractType {
	public function buildForm( FormBuilderInterface $builder, array $options ) {

		$builder->add( 'name', TextType::class, array(
			'attr' => array( 'class' => 'form-control', 'placeholder' => 'Full name' )
		) )->add( 'phone', NumberType::class, array(
			'attr' => array( 'class' => 'form-control', 'placeholder' => 'Phone number' )
		) )->add( 'email', EmailType::class, array(
			'attr' => array( 'class' => 'form-control', 'placeholder' => 'E-mail' )
		) );
	}

	public function configureOptions( OptionsResolver $resolver ) {
		$resolver->setDefaults( array(
			'data_class' => Lead::class,
		) );
	}
}

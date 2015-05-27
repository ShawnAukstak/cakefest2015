<?php
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 */
class UsersTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('users');
        $this->displayField('full_name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Parties', [
            'foreignKey' => 'party_id'
        ]);
        $this->hasMany('Answers', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Questions', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('ShortTitleQuestions', [
            'className' => 'Questions',
            'foreignKey' => 'user_id',
            'conditions' => ['CHAR_LENGTH(ShortTitleQuestions.title) <' => 20]
        ]);

    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');
            
        $validator
            ->allowEmpty('first_name');
            
        $validator
            ->allowEmpty('last_name');
            
        $validator
            ->add('email', 'valid', ['rule' => 'email'])
            ->requirePresence('email', 'create')
            ->notEmpty('email');
            
        $validator
            ->requirePresence('password', 'create')
            ->notEmpty('password')
	    ->add('password', 'minLength', [
                'rule' => ['minLength', 4],
                'message' => 'Passwords should have at least 4 characters'
                ]);

        $validator
            ->add('role', 'valid', ['rule' => 'numeric'])
            ->requirePresence('role', 'create')
            ->notEmpty('role')
            ->add('role', 'valid', [
                'rule' => ['inList', [0,1]],
                'message' => 'Role should be 0 (User) or 1 (Admin)'
                ]);

        $validator
            ->add('password', 'anotherPasswordValidation', [
                'rule' => function ($value, $context) {
                    //do complex validation on password
                    return true;
                }
                ])
            ->add('password', 'strongerPasswordForAdmins', [
                'rule' => ['minLength', 8],
                'message' => 'Admins should have passwords at least 8 chars',
                'on' => function ($context) {
                    return (int)$context['data']['role'] === 1;
                }
                ]);
        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['email']));
        $rules->add($rules->existsIn(['party_id'], 'Parties'));
        return $rules;
    }
}

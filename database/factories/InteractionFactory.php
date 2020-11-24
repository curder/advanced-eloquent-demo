<?php

namespace Database\Factories;

use App\Models\Interaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class InteractionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Interaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTimeThisDecade;

        return [
            'type' => $this->faker->randomElement([
                'Phone',
                'Email',
                'SMS',
                'Meeting',
                'Breakfast',
                'Lunch',
                'Dinner',
                'Twitter',
                'Facebook',
                'LinkedIn',
                'Viewed Invoice',
                'Paid Invoice',
            ]),
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }
}

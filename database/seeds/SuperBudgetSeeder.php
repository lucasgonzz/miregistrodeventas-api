<?php

use App\SuperBudget;
use App\SuperBudgetFeature;
use App\SuperBudgetTitle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SuperBudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $diana = [
            'client' => 'Comision CEF',
            'offer_validity'    => Carbon::now()->addDays(7),
            'hour_price'        => 3500,
            'delivery_time'     => '4 semanas, el tiempo de entrega puede variar dependiendo las revisiones solicitadas por el cliente.',
            'titles'             => [
                [
                    'text' => 'Presupuesto para el desarrollo de Aplicación Web con almacenamiento de datos en la Nube.'
                ],
                [
                    'text' => 'La tecnología en la Nube permite acceder la información desde cualquier dispositivo conectado a internet.'
                ],
                [
                    'text' => 'El desarrollo en esta arquitectura permite que se puedan ir haciendo mejoras en el sistema una que vez el cliente comienza a usarlo, estas mejoras a realizarse, las irá identificando el cliente conforme utilice el programa.'
                ],
                [
                    'text' => 'El soporte de servidores corre por nuestra cuenta, con copias de seguridad diarias de la información cargada en el sistema, por lo que se cobra un mantenimiento anual de $4000.',
                ],
            ],
            'features'          => [
                [
                    'title'             => 'Dar de alta socios',
                    'description'       => 'El usuario administrador podrá dar de alta, editar y eliminar socios dentro del sistema, cada socio contara con uno o mas servicios y un historial de pagos.',
                    'development_time'  => 3,
                ],
                [
                    'title'             => 'Dar de alta Servicios',
                    'description'       => 'Los servicios representan las actividades a las que los socios pueden adherir, constaran de un nombre, precio y opcionalmente una descripción.',
                    'development_time'  => 3,
                ],
                [
                    'title'         => 'Imprimir recibos de pago',
                    'description'   => 'Opción para generar un PDF con los datos del Socio y el Servicio que esta pagando.',
                    'development_time'  => 2,
                ],
                [
                    'title'         => 'Imprimir historial de pago de un socio o de todos los socios',
                    'description'   => 'Opción para generar un PDF con una lista de los pagos que ha ido abonando un Socio, o que han ido abonando todos los socios, en un plazo de tiempo dado.',
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'Dar de alta Proveedores con historial de pagos',
                    'description'       => 'El usuario administrador podrá dar de alta, editar y eliminar proveedores dentro del sistema, cada preveedor contara con un historial de compras, cada compra llevara los datos de la factura: fecha de emision, importe, numero de boleta.',
                    'development_time'  => 4,
                ],
            ],
        ];
        
        $model = $diana;
        $_model = SuperBudget::create([
            'client'            => $model['client'],
            'offer_validity'    => $model['offer_validity'],
            'hour_price'        => $model['hour_price'],
            'delivery_time'     => $model['delivery_time'],
        ]);
        foreach ($model['titles'] as $title) {
            SuperBudgetTitle::create([
                'text'             => $title['text'],
                'super_budget_id'   => $_model->id,
            ]);
        }
        foreach ($model['features'] as $feature) {
            SuperBudgetFeature::create([
                'title'             => $feature['title'],
                'description'       => $feature['description'],
                'development_time'  => $feature['development_time'],
                'super_budget_id'   => $_model->id,
            ]);
        }
    }

    function laBarraca() {
        $la_barraca = [
            'client'            => 'La Barraca',
            'offer_validity'    => Carbon::now()->addDays(7),
            'hour_price'        => 3500,
            'delivery_time'     => '4 semanas, el tiempo de entrega puede variar dependiendo las revisiones solicitadas por el cliente.',
            'features' => [
                [
                    'title'             => 'Stock detallado',
                    'description'       => 'Poder indicar el deposito, la nave, la columna y la altura.
Indicarías el stock total del producto, con 3 columnas auxiliares, para que coloques en cada columna el numero que representa, por un lado a la nave, por otro la columna y por ultimo la altura.',
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'Listas de precios para proveedores',
                    'description'       => '' ,
                    // 'description'       => 'Crear listas de precios para los proveedores, cada una con un nombre y margen de ganancia, para luego aplicar ese marguen de ganancia a los artículos que pertenezcan a esa lista.' ,
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'Indicar las columas antes de importar excel',
                    'description'       => '' ,
                    // 'description'       => 'Luego de cargar el archivo excel con los datos a importar, indicas a que propiedad representa cada letra de las columnas.' ,
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'Adjuntar a un pedido de un proveedor una o mas facturas',
                    'description'       => '' ,
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'Acopios',
                    'description'       => '' ,
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'Crear medios de pago para cada tarjeta',
                    'description'       => 'Crear un medio de pago que represente una tarjeta, con sus diferentes planes según la cantidades de cuotas, y los porcentajes de recargo para cada plan.' ,
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'Varios metodos de pago para una sola venta',
                    'description'       => '' ,
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'Venta sujeta a actualizaciones de precios',
                    'description'       => 'Poder marcar una venta para que se actualicen los precios de los artículos conforme se vayan actualizando los precios en el sistema hasta que el cliente pague esa venta.' ,
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'Poder imprimir los remitos sin el precio de los articulos',
                    'description'       => '' ,
                    // 'description'       => 'Indicar antes de imprimir el remito si imprimir con o sin los precios de los articulos' ,
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'Indicar que se va a hacer la factura antes de guardar la venta',
                    'description'       => '' ,
                    'development_time'  => 1,
                ],
            ],
        ];
    }
}

<?php

use App\SuperBudget;
use App\SuperBudgetFeature;
use App\SuperBudgetFeatureItem;
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
        // $model = $this->canchas_bartolo();
        $model = $this->angelo();
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
            $_feature = SuperBudgetFeature::create([
                'title'             => $feature['title'],
                'description'       => isset($feature['description']) ? $feature['description'] : null,
                'development_time'  => $feature['development_time'],
                'super_budget_id'   => $_model->id,
            ]);
            if (isset($feature['items'])) {
                foreach ($feature['items'] as $item) {
                    SuperBudgetFeatureItem::create([
                        'text'                      => $item,
                        'super_budget_feature_id'   => $_feature->id,
                    ]);
                }
            }
        }
    }

    function angelo() {
        return [
            'client' => 'Angelo Pasteleria',
            'offer_validity'    => Carbon::now()->addDays(7),
            'hour_price'        => 3000,
            'delivery_time'     => '4 semanas, el tiempo de entrega puede variar dependiendo las revisiones solicitadas por el cliente.',
            'titles'             => [
                [
                    'text' => 'Presupuesto para la actualizacion de Aplicación Web con almacenamiento de datos en la Nube.'
                ],
                [
                    'text' => 'La tecnología en la Nube permite acceder la información desde cualquier dispositivo conectado a internet.'
                ],
                [
                    'text' => 'El desarrollo en esta arquitectura permite que se puedan ir haciendo mejoras en el sistema una que vez el cliente comienza a usarlo, estas mejoras a realizarse, las irá identificando el cliente conforme utilice el programa.'
                ],
            ],
            'features'          => [
                [
                    'title'             => 'Solo el usuario administrador podrá ver el total de las ventas',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'Solo el usuario administrador podrá eliminar pedidos',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'Indicar el método de pago para cada venta',
                    'description'       => 'El usuario administrador podrá dar de alta, editar y eliminar METODOS DE PAGO, para luego indicarlo en las ventas.',
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'Indicar el tipo de venta',
                    'description'       => 'El usuario administrador podrá dar de alta, editar y eliminar TIPOS DE VENTA, y asignarle a cada una que cree un color, para luego indicarlo en las ventas. Por ejemplo:',
                    'items'             => [
                        'Tipo de venta: "Salon", color: "Rojo"',
                        'Tipo de venta: "Take away", color: "Naranja"',
                    ],
                    'development_time'  => 3,
                ],
                [
                    'title'             => 'Mostrar con un color amarillo los pedidos entregados',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'Indicar si una venta fue pagada',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'Indicar si una venta fue actualizada',
                    'description'       => 'Los items agregados a un pedido aparecerán resaltados, el pedido aparecera con un aviso de que fue modificado y se actualizaran las demás computadoras con una notificación del pedido que fue modificado.',
                    'development_time'  => 2,
                ],
            ],
        ];
    }

    function canchas_bartolo() {
        return [
            'client' => 'Bartolome Kablan',
            'offer_validity'    => Carbon::now()->addDays(7),
            'hour_price'        => 3500,
            'delivery_time'     => '6 semanas, el tiempo de entrega puede variar dependiendo las revisiones solicitadas por el cliente.',
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
                    'text' => 'El soporte de servidores corre por nuestra cuenta, con copias de seguridad diarias de la información cargada en el sistema. Soporte para que ingresen los propietarios, empleados del negocio y los clientes, por lo que se cobra un mantenimiento mensual de $1000. Además se brindara un dominio, escogido por el cliente, para que accedan los sus clientes, este dominio tiene un costo de renovación anual de $4000.',
                ],
            ],
            'features'          => [
                [
                    'title'             => 'Dar de alta canchas',
                    'description'       => 'El usuario administrador podrá dar de alta, editar y eliminar canchas dentro del sistema, cada cancha constara de un nombre y una descripción.',
                    'development_time'  => 3,
                ],
                [
                    'title'             => 'Dar de alta Horarios',
                    'description'       => 'El usuario administrador podrá dar de alta, editar y eliminar horarios dentro del sistema, cada horario constara de un nombre y una descripción.',
                    'development_time'  => 3,
                ],
                [
                    'title'             => 'Vincular las canchas con los horarios',
                    'description'       => 'Una vez creada, por ejemplo, la cancha "Futbol" y los horarios "Tarde" y "Noche", podrá vincular la cancha Futbol al horario Tarde, asignando la duración en horas del turno y un precio por la duración asignada, y a la misma cancha vincularla al horario Noche con otro precio distinto. Todas las canchas van a poder vincularse con todos los horarios dados de alta.',
                    'development_time'  => 3,
                ],
                [
                    'title'             => 'Dar de alta turnos para las canchas',
                    'description'       => 'El usuario podrá crear turnos para las canchas, que representaran los alquileres de las mismas. Los pasos para dar de alta un turno son:',
                    'items'             => [
                        'Escoger la cancha y la fecha.',
                        'En base a la cancha y la fecha seleccionadas se mostrarían los horarios/turnos disponibles.',
                        'Una vez seleccionado el horario, podrá seleccionar el método de pago.',
                        'Luego de indicar la información anterior, se dará de alta el turno.'
                    ],
                    'development_time'  => 5,
                ],
                [
                    'title'             => 'Dar de alta productos',
                    'description'       => 'El usuario podrá dar de alta, editar y eliminar productos dentro del sistema, cada producto constara de un nombre, costo, precio y stock. Todo el conjunto de productos conformara el inventario.',
                    'development_time'  => 3,
                ],
                [
                    'title'             => 'Crear ventas de productos',
                    'description'       => 'El usuario podrá crear ventas, las ventas estaran conformadas por los productos previamente dados de alta, a los cuales indicara la cantidad al mometo de agregar a una venta.',
                    'items'             => [
                        'Las ventas solo estarán conformadas por productos.',
                        'No tendran la posibilidad de asignar un cliente.',
                        'No tendran la posibilidad de imprimirlas.',
                        'Tendran la posibilidad de indicar que empleado la realizo.',
                    ],
                    'development_time'  => 5,
                ],
                [
                    'title'             => 'Sección CAJA',
                    'description'       => 'Habrá una sección para ver los movimientos de la CAJA, aquí se visualizaran las ventas de la cantina, los turnos dados de alta, y un resumen de los metodos de pago utilizados y los montos para cada método, tanto para la cantina como para los alquileres.',
                    'development_time'  => 5,
                ],
                [
                    'title'             => 'Dar de alta empleados',
                    'description'       => 'Se podrán crear, editar y eliminar empleados, con el fin de darles acceso a las distintas áreas dentro del sistema y que cada uno pueda dejar registro de las ventas que realizo.',
                    'development_time'  => 4,
                ],
                [
                    'title'             => 'Pagina para el ingreso de los clientes y reserva de canchas',
                    'description'       => 'Pagina alojada en un dominio a elección del cliente, por ejemplo "canchas-bartolo.com", a la que ingresaran los clientes, con la opción para reservar una cancha. Los pasos serian:',
                    'items'             => [
                        'Escoger la cancha y la fecha.',
                        'En base a la cancha y la fecha seleccionadas se mostrarían los horarios disponibles.',
                        'Una vez seleccionado el horario, tendrá la única opción de abonar el total con su cuenta de MercadoPago.',
                        'Luego de recibir el pago, se informaría mediante mail al cliente de la correcta reservación de la cancha y se actualizaría la lista de reservas en la parte del negocio.'
                    ],
                    'development_time'  => 8,
                ],
            ],
        ];
    }
 
    function diana() {
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

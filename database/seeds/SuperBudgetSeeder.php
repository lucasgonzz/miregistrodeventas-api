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
        $model = $this->scrap_free();
        // $model = $this->angelo();
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

    function scrap_free() {
        return [
            'client' => 'Scrap Free',
            'offer_validity'    => Carbon::now()->addDays(7),
            'hour_price'        => 3500,
            'delivery_time'     => '10 semanas, el tiempo de entrega puede variar dependiendo las revisiones solicitadas por el cliente.',
            'titles'             => [
                [
                    'text' => 'Presupuesto para el desarrollo de Aplicación Web con almacenamiento de datos en la Nube.'
                ],
                [
                    'text' => 'La tecnología en la Nube permite acceder la información desde cualquier dispositivo conectado a internet.'
                ],
                [
                    'text'  => 'Para que sea mas ameno, solo se detallaran las propiedades de las Entidades que estén vinculadas a otras Entidades.',
                ],
                [
                    'text'  => 'Solo se detallaran la funcionalidades utilizadas por los empleados, faltaría examinar las herramientas utilizadas por el administrador (Mauro), como los mapas de calor y demás para agregarlas al presupuesto.',
                ],
            ],
            'features'          => [
                [
                    'title'             => 'CRUD Asegurados',
                    'description'       => 'Se podrán crear, editar y eliminar Asegurados, para luego vincularlos a un Siniestro.',
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'CRUD Aseguradoras',
                    'description'       => 'Se podrán crear, editar y eliminar Aseguradoras, para luego vincularlas a un Siniestro.',
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'CRUD Honorarios de Liquidacion',
                    'description'       => 'Se podrán crear, editar y eliminar Honorarios de Liquidacion, para luego vincularlos a las Aseguradoras.',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Unidades de Negocio',
                    'description'       => 'Se podrán crear, editar y eliminar Unidades de Negocio, para luego vincularlos a los Gestores.',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Gestores de aseguradoras',
                    'description'       => 'Se podrán crear, editar y eliminar Gestores, para luego vincularlos a una Aseguradora y a un Siniestro.',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Gestores de Scrap Free',
                    'description'       => 'Se podrán crear, editar y eliminar Gestores, para luego vincularlos a un Siniestro y a una Unidad de Negocio.',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Provincias',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Localidades',
                    'description'       => 'Se podrán crear, editar y eliminar Localidades, para luego vincularlas a una Provincia.',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Lineas',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Sub Lineas',
                    'description'       => 'Se podrán crear, editar y eliminar Sub Lineas, para luego vincularlas a una Linea, y luego vincularas a un Bien.',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Estados de Bienes',
                    'description'       => 'Se podrán crear, editar y eliminar Estados de Bienes, para luego vincularlos a un Bien.',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Causas Bien',
                    'description'       => 'Se podrán crear, editar y eliminar Causas de un Bien, para luego vincularlas a un Bien.',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Bienes',
                    'description'       => 'Se podrán crear, editar y eliminar Bienes, para luego vincularlos a:',
                    'items'             => [
                        'Un Siniestro',
                        'Una Linea y SubLinea',
                        'Estado de bien',
                        'Causa Bien',
                        'Tecnico Asegurado',
                        'Tecnico Scrap Free',
                        'Logistica',
                    ],
                    'development_time'  => 4,
                ],
                [
                    'title'             => 'CRUD Polizas',
                    'description'       => 'Se podrán crear, editar y eliminar Polizas, para luego vincularlas a un Asegurado.',
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'CRUD Coberturas',
                    'description'       => 'Se podrán crear, editar y eliminar Coberturas, para luego vincularlas a una Poliza.',
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'CRUD Tipos de Orden de Servicio',
                    'description'       => 'Se podrán crear, editar y eliminar Tipos de Orden de Servicio, para luego vincularlas a un Siniestro.',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Causas de Siniestro',
                    'description'       => 'Se podrán crear, editar y eliminar Causas de Siniestro, para luego vincularlas a un Siniestro.',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Estados de Siniestro',
                    'description'       => 'Se podrán crear, editar y eliminar Estados de Siniestro, para luego vincularlos a un Siniestro.',
                    'items'             => [
                        'El Siniestro pertenecera a un solo Estado por vez.',
                        'No obstante, se guardara registro de todos los estados por los que ha pasado',
                        'Tambien se dejara registro de el tiempo que permanecio en cada estado por los cuales paso.'
                    ],
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'CRUD Siniestros',
                    'description'       => 'Se podrán crear, editar y eliminar Siniestros, este a su vez estará vinculado a las siguientes entidades:',
                    'items'             => [
                        'Aseguradora',
                        'Asegurado',
                        'Causa Siniestro',
                        'Estado Siniestro',
                        'Provincia',
                        'Localidad',
                        'Tipo de Orden de Servicio',
                        'Gestor Aseguradora',
                        'Gestor Scrap Free',
                    ],
                    'development_time'  => 4,
                ],
                [
                    'title'             => 'CRUD Transportistas',
                    'description'       => 'Se podrán crear, editar y eliminar Transportistas, para luego vincularlos a una Logistica.',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Logisticas',
                    'description'       => 'Se podrán crear, editar y eliminar Logisticas, para luego vincularlas a un Siniestro o a un Bien. A su vez una Logistica estara vinculada con:',
                    'items'             => [
                        'Transporte Retiro',
                        'Transporte Devolucion',
                        'Siniestro',
                        'Bien',
                    ],
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'CRUD Tecnicos',
                    'description'       => 'Se podrán crear, editar y eliminar Tecnicos, para luego vincularlos a un Informe Tecnico.',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Causas Probables',
                    'description'       => 'Se podrán crear, editar y eliminar Causas Probables, para luego vincularlas a un Informe Tecnico.',
                    'development_time'  => 1,
                ],
                [
                    'title'             => 'CRUD Informes Tecnicos',
                    'description'       => 'Se podrán crear, editar y eliminar Informes Tecnicos, que estaran vinculados con las siguientes Entidades:',
                    [
                        'Tecnico',
                        'Causa Probable',
                        'Bien',
                        'Siniestro',
                    ],
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'Sección Siniestros',
                    'description'       => 'En esta sección se listaran todos los Siniestros en proceso, ordenados por los Estados de Siniestro a los que pertenezcan, con la opción de verlos en detalle y editarlos. Se mostraran las propiedades de cada siniestro junto con la siguiente información:',
                    [
                        'Hace cuantos dias se dio de alta en el sistema.',
                        'Hace cuantos dias esta en el actual estado.',
                    ],
                    'development_time'  => 4,
                ],
                [
                    'title'             => 'Creacion de Plantillas de Emails',
                    'description'       => 'Se podrán dar de alta, editar y eliminar Plantillas de Emails, cada una vinculada a un Estado de Siniestro, para que cada vez que se actualice el Estado de Siniestro de un Siniestro, se procederá a informar mediante email al Asegurado, utilizando la plantilla preformateada del nuevo Estado de Siniestro',
                    'items'             => [
                        'La idea es que se deje asentado un formato de mensaje preestablecido para cada instancia a la que va avanzando un Siniestro.',
                        'Puede que para el primer Estado de Siniestro "Contactar Asegurado" el mensaje preformateado sea: "Buenos dias {asegurado.nombre}, nos contacamos de Scrap Free para informarle que estaremos procesando su siniestro dado de alta en {asegurado.aseguradora.nombre}".',
                        'Así mismo, cuando el Estado de Siniestro se actualice a "Pendiente Informe Tecnico", el mensaje preformateado sera: "Hola {asegurado.nombre}, el tecnico {siniestro.tecnico.nombre} esta evaluando la condicion de su {siniestro.bien.nombre}"',
                    ],
                    'development_time'  => 10,
                ],
                [
                    'title'             => 'Generación automática de PDF',
                    'description'       => 'Podrán generarse documentos PDF, utilizando una plantilla previamente programada, en la cual se detallaría la información que se necesite y el PDF generado se insertaría en un Email. Estas plantillas PDF tendrían que especificarse de antemanto para proceder a su confección y dejarlas listas para su uso. Cada plantilla tendría un tiempo de desarrollo estimado de 1hs.',
                    'development_time'  => 0,
                ],
            ],
        ];
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
                    'title'             => 'Dar de alta descuentos para ventas',
                    'description'       => 'Se podrán crear, editar y eliminar descuentos para asignarles a una nueva venta.',
                    'development_time'  => 2,
                ],
                [
                    'title'             => 'Crear ventas de productos',
                    'description'       => 'El usuario podrá crear ventas, las ventas estaran conformadas por los productos previamente dados de alta, a los cuales indicara la cantidad al mometo de agregar a una venta.',
                    'items'             => [
                        'Las ventas solo estarán conformadas por productos.',
                        'No tendran la posibilidad de asignar un cliente.',
                        'No tendran la posibilidad de imprimirlas.',
                        'Tendran la posibilidad de indicar que empleado la realizo.',
                        'Se le podrán aplicar uno o mas descuentos.',
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
                [
                    'title'             => 'Dar de alta socios y suscripciones',
                    'description'       => 'Se podrán crear, editar y eliminar socios y suscripciones de MercadoPago, con el fin de vincular a un socio con una suscripción para que se le debite automáticamente el monto indicado en la suscripción creada.',
                    'development_time'  => 5,
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

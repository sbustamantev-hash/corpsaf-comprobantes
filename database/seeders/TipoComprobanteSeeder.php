<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoComprobante;

class TipoComprobanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Tipos de comprobante de pago o documento según SUNAT (Perú)
     */
    public function run(): void
    {
        $tiposComprobante = [
            ['codigo' => '00', 'descripcion' => 'Otros', 'activo' => true],
            ['codigo' => '01', 'descripcion' => 'Factura', 'activo' => true],
            ['codigo' => '02', 'descripcion' => 'Recibo por Honorarios', 'activo' => true],
            ['codigo' => '03', 'descripcion' => 'Boleta de Venta', 'activo' => true],
            ['codigo' => '04', 'descripcion' => 'Liquidación de compra', 'activo' => true],
            ['codigo' => '05', 'descripcion' => 'Boleto de compañía de aviación comercial por el servicio de transporte aéreo de pasajeros', 'activo' => true],
            ['codigo' => '06', 'descripcion' => 'Carta de porte aéreo por el servicio de transporte de carga aérea', 'activo' => true],
            ['codigo' => '07', 'descripcion' => 'Nota de crédito', 'activo' => true],
            ['codigo' => '08', 'descripcion' => 'Nota de débito', 'activo' => true],
            ['codigo' => '09', 'descripcion' => 'Guía de remisión - Remitente', 'activo' => true],
            ['codigo' => '10', 'descripcion' => 'Recibo por Arrendamiento', 'activo' => true],
            ['codigo' => '11', 'descripcion' => 'Póliza emitida por las Bolsas de Valores, Bolsas de Productos o Agentes de Intermediación por operaciones realizadas en las Bolsas de Valores o Productos o fuera de las mismas, autorizadas por CONASEV', 'activo' => true],
            ['codigo' => '12', 'descripcion' => 'Ticket o cinta emitido por máquina registradora', 'activo' => true],
            ['codigo' => '13', 'descripcion' => 'Documento emitido por bancos, instituciones financieras, crediticias y de seguros que se encuentren bajo el control de la Superintendencia de Banca y Seguros', 'activo' => true],
            ['codigo' => '14', 'descripcion' => 'Recibo por servicios públicos de suministro de energía eléctrica, agua, teléfono, telex y telegráficos y otros servicios complementarios que se incluyan en el recibo de servicio público', 'activo' => true],
            ['codigo' => '15', 'descripcion' => 'Boleto emitido por las empresas de transporte público urbano de pasajeros', 'activo' => true],
            ['codigo' => '16', 'descripcion' => 'Boleto de viaje emitido por las empresas de transporte público interprovincial de pasajeros dentro del país', 'activo' => true],
            ['codigo' => '17', 'descripcion' => 'Documento emitido por la Iglesia Católica por el arrendamiento de bienes inmuebles', 'activo' => true],
            ['codigo' => '18', 'descripcion' => 'Documento emitido por las Administradoras Privadas de Fondo de Pensiones que se encuentran bajo la supervisión de la Superintendencia de Administradoras Privadas de Fondos de Pensiones', 'activo' => true],
            ['codigo' => '19', 'descripcion' => 'Boleto o entrada por atracciones y espectáculos públicos', 'activo' => true],
            ['codigo' => '20', 'descripcion' => 'Comprobante de Retención', 'activo' => true],
            ['codigo' => '21', 'descripcion' => 'Conocimiento de embarque por el servicio de transporte de carga marítima', 'activo' => true],
            ['codigo' => '22', 'descripcion' => 'Comprobante por Operaciones No Habituales', 'activo' => true],
            ['codigo' => '23', 'descripcion' => 'Pólizas de Adjudicación emitidas con ocasión del remate o adjudicación de bienes por venta forzada, por los martilleros o las entidades que rematen o subasten bienes por cuenta de terceros', 'activo' => true],
            ['codigo' => '24', 'descripcion' => 'Certificado de pago de regalías emitidas por PERUPETRO S.A', 'activo' => true],
            ['codigo' => '25', 'descripcion' => 'Documento de Atribución (Ley del Impuesto General a las Ventas e Impuesto Selectivo al Consumo, Art. 19°, último párrafo, R.S. N° 022-98-SUNAT)', 'activo' => true],
            ['codigo' => '26', 'descripcion' => 'Recibo por el Pago de la Tarifa por Uso de Agua Superficial con fines agrarios y por el pago de la Cuota para la ejecución de una determinada obra o actividad acordada por la Asamblea General de la Comisión de Regantes o Resolución expedida por el Jefe de la Unidad de Aguas y de Riego (Decreto Supremo N° 003-90-AG, Arts. 28 y 48)', 'activo' => true],
            ['codigo' => '27', 'descripcion' => 'Seguro Complementario de Trabajo de Riesgo', 'activo' => true],
            ['codigo' => '28', 'descripcion' => 'Tarifa Unificada de Uso de Aeropuerto', 'activo' => true],
            ['codigo' => '29', 'descripcion' => 'Documentos emitidos por la COFOPRI en calidad de oferta de venta de terrenos, los correspondientes a las subastas públicas y a la retribución de los servicios que presta', 'activo' => true],
            ['codigo' => '30', 'descripcion' => 'Documentos emitidos por las empresas que desempeñan el rol adquirente en los sistemas de pago mediante tarjetas de crédito y débito', 'activo' => true],
            ['codigo' => '31', 'descripcion' => 'Guía de Remisión - Transportista', 'activo' => true],
            ['codigo' => '32', 'descripcion' => 'Documentos emitidos por las empresas recaudadoras de la denominada Garantía de Red Principal a la que hace referencia el numeral 7.6 del artículo 7º de la Ley N° 27133 – Ley de Promoción del Desarrollo de la Industria del Gas Natural', 'activo' => true],
            ['codigo' => '34', 'descripcion' => 'Documento del Operador', 'activo' => true],
            ['codigo' => '35', 'descripcion' => 'Documento del Partícipe', 'activo' => true],
            ['codigo' => '36', 'descripcion' => 'Recibo de Distribución de Gas Natural', 'activo' => true],
            ['codigo' => '37', 'descripcion' => 'Documentos que emitan los concesionarios del servicio de revisiones técnicas vehiculares, por la prestación de dicho servicio', 'activo' => true],
            ['codigo' => '40', 'descripcion' => 'Constancia de Depósito - IVAP (Ley 28211)', 'activo' => true],
            ['codigo' => '50', 'descripcion' => 'Declaración Única de Aduanas - Importación definitiva', 'activo' => true],
            ['codigo' => '52', 'descripcion' => 'Despacho Simplificado - Importación Simplificada', 'activo' => true],
            ['codigo' => '53', 'descripcion' => 'Declaración de Mensajería o Courier', 'activo' => true],
            ['codigo' => '54', 'descripcion' => 'Liquidación de Cobranza', 'activo' => true],
            ['codigo' => '87', 'descripcion' => 'Nota de Crédito Especial', 'activo' => true],
            ['codigo' => '88', 'descripcion' => 'Nota de Débito Especial', 'activo' => true],
            ['codigo' => '91', 'descripcion' => 'Comprobante de No Domiciliado', 'activo' => true],
            ['codigo' => '96', 'descripcion' => 'Exceso de crédito fiscal por retiro de bienes', 'activo' => true],
            ['codigo' => '97', 'descripcion' => 'Nota de Crédito - No Domiciliado', 'activo' => true],
            ['codigo' => '98', 'descripcion' => 'Nota de Débito - No Domiciliado', 'activo' => true],
            ['codigo' => '99', 'descripcion' => 'Otros - Consolidado de Boletas de Venta', 'activo' => true],
        ];

        foreach ($tiposComprobante as $tipo) {
            TipoComprobante::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                $tipo
            );
        }
    }
}

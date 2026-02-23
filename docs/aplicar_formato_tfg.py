"""
aplicar_formato_tfg.py
======================
Aplica el formato estándar del TFG al Anteproyecto.docx usando python-docx.

Requisito:
    pip install python-docx

Uso:
    python aplicar_formato_tfg.py

El script NO sobreescribe el original.
Genera: Anteproyecto_Formateado.docx
"""

import os
from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_LINE_SPACING
from docx.enum.style import WD_STYLE_TYPE
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

# ─────────────────────────────────────────
# RUTAS
# ─────────────────────────────────────────
RUTA_ENTRADA = r"C:\Users\Usuario\Documents\DAM2\TFG\Anteproyecto.docx"
RUTA_SALIDA  = r"C:\Users\Usuario\Documents\DAM2\TFG\Anteproyecto_Formateado.docx"

# ─────────────────────────────────────────
# COLORES
# ─────────────────────────────────────────
AZUL_OSCURO = RGBColor(0x1F, 0x38, 0x64)   # Títulos H1 y H2
NEGRO       = RGBColor(0x00, 0x00, 0x00)   # Cuerpo y H3


# ══════════════════════════════════════════════════════════════
# PÁGINA — Márgenes y tamaño A4
# ══════════════════════════════════════════════════════════════
def configurar_pagina(doc):
    for section in doc.sections:
        section.page_height   = Cm(29.7)
        section.page_width    = Cm(21.0)
        section.top_margin    = Cm(2.5)
        section.bottom_margin = Cm(2.5)
        section.left_margin   = Cm(3.0)   # Margen encuadernación
        section.right_margin  = Cm(2.5)
    print("  [OK] Página A4 y márgenes configurados")


# ══════════════════════════════════════════════════════════════
# ESTILOS DE TEXTO
# ══════════════════════════════════════════════════════════════
def estilo_normal(doc):
    """Calibri 11pt, negro, justificado, interlineado 1,5, 6pt después."""
    s  = doc.styles['Normal']
    f  = s.font
    pf = s.paragraph_format

    f.name       = 'Calibri'
    f.size       = Pt(11)
    f.bold       = False
    f.italic     = False
    f.color.rgb  = NEGRO

    pf.alignment         = WD_ALIGN_PARAGRAPH.JUSTIFY
    pf.line_spacing_rule = WD_LINE_SPACING.ONE_POINT_FIVE
    pf.space_after       = Pt(6)
    pf.space_before      = Pt(0)
    pf.first_line_indent = Cm(0)
    print("  [OK] Estilo Normal")


def estilo_h1(doc):
    """Calibri 18pt, negrita, centrado, azul oscuro."""
    s  = doc.styles['Heading 1']
    f  = s.font
    pf = s.paragraph_format

    f.name       = 'Calibri'
    f.size       = Pt(18)
    f.bold       = True
    f.italic     = False
    f.color.rgb  = AZUL_OSCURO

    pf.alignment      = WD_ALIGN_PARAGRAPH.CENTER
    pf.space_before   = Pt(24)
    pf.space_after    = Pt(12)
    pf.keep_with_next = True
    print("  [OK] Estilo Heading 1")


def estilo_h2(doc):
    """Calibri 14pt, negrita, izquierda, azul oscuro."""
    s  = doc.styles['Heading 2']
    f  = s.font
    pf = s.paragraph_format

    f.name       = 'Calibri'
    f.size       = Pt(14)
    f.bold       = True
    f.italic     = False
    f.color.rgb  = AZUL_OSCURO

    pf.alignment      = WD_ALIGN_PARAGRAPH.LEFT
    pf.space_before   = Pt(18)
    pf.space_after    = Pt(6)
    pf.keep_with_next = True
    print("  [OK] Estilo Heading 2")


def estilo_h3(doc):
    """Calibri 12pt, negrita + cursiva, izquierda, negro."""
    s  = doc.styles['Heading 3']
    f  = s.font
    pf = s.paragraph_format

    f.name       = 'Calibri'
    f.size       = Pt(12)
    f.bold       = True
    f.italic     = True
    f.color.rgb  = NEGRO

    pf.alignment      = WD_ALIGN_PARAGRAPH.LEFT
    pf.space_before   = Pt(12)
    pf.space_after    = Pt(6)
    pf.keep_with_next = True
    print("  [OK] Estilo Heading 3")


# ══════════════════════════════════════════════════════════════
# ESTILOS PERSONALIZADOS
# ══════════════════════════════════════════════════════════════
def _get_o_crear_estilo(doc, nombre):
    """Devuelve el estilo si existe; si no, lo crea."""
    try:
        return doc.styles[nombre]
    except KeyError:
        return doc.styles.add_style(nombre, WD_STYLE_TYPE.PARAGRAPH)


def estilo_bibliografia(doc):
    """
    Calibri 10pt, izquierda, sangría francesa 1,27 cm.
    Uso: seleccionar cada referencia y aplicar estilo 'TFG_Bibliografia'
    """
    s = _get_o_crear_estilo(doc, 'TFG_Bibliografia')
    s.base_style = doc.styles['Normal']

    f  = s.font
    pf = s.paragraph_format

    f.name      = 'Calibri'
    f.size      = Pt(10)
    f.color.rgb = NEGRO
    f.bold      = False
    f.italic    = False

    pf.alignment         = WD_ALIGN_PARAGRAPH.LEFT
    pf.first_line_indent = Cm(-1.27)   # Sangría francesa
    pf.left_indent       = Cm(1.27)
    pf.space_after       = Pt(4)
    pf.line_spacing_rule = WD_LINE_SPACING.SINGLE
    print("  [OK] Estilo TFG_Bibliografia")


def estilo_cita_larga(doc):
    """
    Calibri 10pt, justificado, sangría 1,27 cm izq. y dcha.
    Uso: citas textuales de más de 40 palabras (sin comillas)
    """
    s = _get_o_crear_estilo(doc, 'TFG_CitaLarga')
    s.base_style = doc.styles['Normal']

    f  = s.font
    pf = s.paragraph_format

    f.name      = 'Calibri'
    f.size      = Pt(10)
    f.color.rgb = NEGRO

    pf.alignment   = WD_ALIGN_PARAGRAPH.JUSTIFY
    pf.left_indent = Cm(1.27)
    pf.right_indent= Cm(1.27)
    pf.space_after = Pt(6)
    pf.line_spacing_rule = WD_LINE_SPACING.SINGLE
    print("  [OK] Estilo TFG_CitaLarga")


def estilo_titulo_tabla(doc):
    """
    Calibri 10pt, negrita, izquierda.
    Uso: 'Tabla N. Descripción' — se coloca ENCIMA de la tabla.
    """
    s = _get_o_crear_estilo(doc, 'TFG_TituloTabla')
    s.base_style = doc.styles['Normal']

    f  = s.font
    pf = s.paragraph_format

    f.name      = 'Calibri'
    f.size      = Pt(10)
    f.bold      = True
    f.color.rgb = NEGRO

    pf.alignment  = WD_ALIGN_PARAGRAPH.LEFT
    pf.space_before = Pt(12)
    pf.space_after  = Pt(3)
    print("  [OK] Estilo TFG_TituloTabla")


def estilo_titulo_figura(doc):
    """
    Calibri 10pt, negrita, centrado.
    Uso: 'Figura N. Descripción' — se coloca DEBAJO de la imagen.
    """
    s = _get_o_crear_estilo(doc, 'TFG_TituloFigura')
    s.base_style = doc.styles['Normal']

    f  = s.font
    pf = s.paragraph_format

    f.name      = 'Calibri'
    f.size      = Pt(10)
    f.bold      = True
    f.color.rgb = NEGRO

    pf.alignment  = WD_ALIGN_PARAGRAPH.CENTER
    pf.space_before = Pt(3)
    pf.space_after  = Pt(12)
    print("  [OK] Estilo TFG_TituloFigura")


# ══════════════════════════════════════════════════════════════
# PIE DE PÁGINA — Número de página alineado a la derecha
# ══════════════════════════════════════════════════════════════
def agregar_numero_pagina(doc):
    """Inserta campo PAGE de Word en el pie, alineado a la derecha."""
    for section in doc.sections:
        footer = section.footer
        # Limpiar contenido previo del pie
        for p in footer.paragraphs:
            p.clear()

        p = footer.paragraphs[0] if footer.paragraphs else footer.add_paragraph()
        p.alignment = WD_ALIGN_PARAGRAPH.RIGHT

        run = p.add_run()

        # Construir campo PAGE mediante XML de Word
        fldChar_ini = OxmlElement('w:fldChar')
        fldChar_ini.set(qn('w:fldCharType'), 'begin')

        instrText = OxmlElement('w:instrText')
        instrText.text = ' PAGE '

        fldChar_fin = OxmlElement('w:fldChar')
        fldChar_fin.set(qn('w:fldCharType'), 'end')

        run._r.append(fldChar_ini)
        run._r.append(instrText)
        run._r.append(fldChar_fin)

        run.font.name      = 'Calibri'
        run.font.size      = Pt(10)
        run.font.color.rgb = NEGRO

    print("  [OK] Número de página en pie de página")


# ══════════════════════════════════════════════════════════════
# MAIN
# ══════════════════════════════════════════════════════════════
def main():
    if not os.path.exists(RUTA_ENTRADA):
        print(f"\n[ERROR] No se encuentra el archivo de entrada:")
        print(f"        {RUTA_ENTRADA}")
        print("\nVerifica que el nombre del archivo sea exactamente 'Anteproyecto.docx'")
        return

    print("\n==========================================")
    print("   Aplicando formato TFG - CONTENTIO")
    print("==========================================\n")

    print("Abriendo documento original...")
    doc = Document(RUTA_ENTRADA)

    print("\n[1/4] Configurando página...")
    configurar_pagina(doc)

    print("\n[2/4] Aplicando estilos base...")
    estilo_normal(doc)
    estilo_h1(doc)
    estilo_h2(doc)
    estilo_h3(doc)

    print("\n[3/4] Creando estilos personalizados...")
    estilo_bibliografia(doc)
    estilo_cita_larga(doc)
    estilo_titulo_tabla(doc)
    estilo_titulo_figura(doc)

    print("\n[4/4] Añadiendo número de página...")
    agregar_numero_pagina(doc)

    print(f"\nGuardando resultado en:")
    print(f"  {RUTA_SALIDA}")
    doc.save(RUTA_SALIDA)

    print("""
==============================================================
  COMPLETADO
==============================================================

  El archivo original NO ha sido modificado.

  Abre Anteproyecto_Formateado.docx y verifica que:
    - Margenes correctos (Disposicion > Margenes)
    - Titulos en azul oscuro y tamano correcto
    - Numero de pagina en pie derecho

  Estilos nuevos disponibles en el documento:
    - TFG_Bibliografia  para las referencias APA
    - TFG_CitaLarga     para citas de mas de 40 palabras
    - TFG_TituloTabla   para 'Tabla N. Descripcion'
    - TFG_TituloFigura  para 'Figura N. Descripcion'

==============================================================
""")


if __name__ == '__main__':
    main()

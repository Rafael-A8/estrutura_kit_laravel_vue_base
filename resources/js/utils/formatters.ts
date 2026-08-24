/**
 * Formatadores de exibição (pt-BR). Só formatam para leitura — nunca use para preparar
 * payload de envio ao backend.
 */

export const formatPhone = (phone?: string): string => {
    if (!phone) {
        return '-';
    }

    const cleaned = phone.replace(/\D/g, '');

    if (cleaned.length === 11) {
        return cleaned.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    }

    if (cleaned.length === 10) {
        return cleaned.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    }

    return phone;
};

export const formatCPF = (cpf?: string): string => {
    if (!cpf) {
        return '-';
    }

    return cpf
        .replace(/\D/g, '')
        .replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
};

export const formatCNPJ = (cnpj?: string): string => {
    if (!cnpj) {
        return '-';
    }

    return cnpj
        .replace(/\D/g, '')
        .replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
};

export const formatCEP = (cep?: string): string => {
    if (!cep) {
        return '-';
    }

    return cep.replace(/\D/g, '').replace(/(\d{5})(\d{3})/, '$1-$2');
};

/** Escolhe o formato pela quantidade de dígitos — 11 vira CPF, 14 vira CNPJ. */
export const formatCPFCNPJ = (document?: string): string => {
    if (!document) {
        return '-';
    }

    const cleaned = document.replace(/\D/g, '');

    if (cleaned.length === 11) {
        return formatCPF(cleaned);
    }

    if (cleaned.length === 14) {
        return formatCNPJ(cleaned);
    }

    return document;
};

export const formatCurrency = (value: number): string =>
    new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);

export const formatNumber = (value: number): string =>
    new Intl.NumberFormat('pt-BR', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);

/**
 * Formata uma data ISO. Lê os componentes da string em vez de instanciar `Date`, para não
 * deslocar o dia por fuso horário.
 */
export const formatDate = (
    date?: string | Date | null,
    format: string = 'DD/MM/YYYY',
): string => {
    if (!date) {
        return '-';
    }

    const dateStr = typeof date === 'string' ? date : date.toISOString();
    const match = dateStr.match(/^(\d{4})-(\d{2})-(\d{2})/);

    if (!match) {
        return '-';
    }

    const [, year, month, day] = match;

    return format
        .replace('YYYY', year)
        .replace('YY', year.slice(2))
        .replace('MM', month)
        .replace('DD', day);
};

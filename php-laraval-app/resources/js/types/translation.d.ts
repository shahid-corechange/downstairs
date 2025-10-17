export default interface Translations {
  en_US: Record<string, Translation>;
  sv_SE: Record<string, Translation>;
  nn_NO: Record<string, Translation>;
}

export interface Translation {
  id: number;
  value: string;
}

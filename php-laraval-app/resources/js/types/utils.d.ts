import { DeepKeys as DeepKeysOf } from "@tanstack/react-table";

export type DeepKeys<T> = DeepKeysOf<T>;

export type LooseDeepKeys<T> = DeepKeysOf<T> | (string & NonNullable<unknown>);

export type Meta = Record<string, string | number | boolean>;

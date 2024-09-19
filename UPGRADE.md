# Upgrade Guide

## General Notes

## Upgrading To 2.0 from 1.x

### Signature changes

Previously, `Model::massUpdate()` expected the `$values` parameter to be an instance of `array` or `Arrayable`,
however, proper casting was never implemented for the latter.

To better reflect the internal requirements, the `$values` parameter is now required to be an instance of `array` or
[`Enumerable`](https://github.com/laravel/framework/blob/v10.13.0/src/Illuminate/Collections/Enumerable.php).



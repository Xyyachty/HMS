<?php

namespace App\Support;

/**
 * Reads a class list out of a spreadsheet.
 *
 * Two shapes arrive here. One is the template this app hands out — a header on the
 * first row with student_id / first_name / last_name / email / phone_number. The other
 * is the registrar's official class list, which nobody is going to retype: the header
 * sits eleven rows down under a school letterhead, the columns are titled the way a
 * registrar titles them ("STUD NO.", "CONTACT #"), the name is one "LAST, FIRST M."
 * cell, and the students are split into Female and Male blocks with a divider row and
 * a "NOTHING FOLLOWS" line at the end.
 *
 * Only four columns are ever read: student number, name, email and contact number.
 * Course, year level, date enrolled and status are the registrar's business.
 */
class StudentRosterSheet
{
    /** Column headings, in the order they are tried. Compared after normalize(). */
    private const HEADINGS = [
        'student_number' => ['stud no', 'student no', 'student number', 'studno', 'student id', 'studentid', 'student_id', 'id no', 'id number'],
        'name'           => ['name', 'full name', 'student name', 'complete name'],
        'first_name'     => ['first name', 'firstname', 'first_name', 'given name'],
        'middle_name'    => ['middle name', 'middlename', 'middle_name', 'middle initial'],
        'last_name'      => ['last name', 'lastname', 'last_name', 'surname', 'family name'],
        'email'          => ['email', 'email address', 'e mail', 'email_address'],
        'phone_number'   => ['contact', 'contact no', 'contact number', 'contact #', 'phone', 'phone no', 'phone number', 'phone_number', 'mobile', 'mobile no', 'mobile number', 'cellphone', 'cell no'],
    ];

    /** Rows that are structure, not people. */
    private const NOT_A_STUDENT = ['female', 'male', 'total', 'nothing follows'];

    /**
     * @param  array<int, array<int, mixed>>  $rows  sheet as a plain numeric-indexed grid
     * @return array{header_row: int|null, columns: array<string, int>, students: array<int, array<string, string>>}
     */
    public static function parse(array $rows): array
    {
        [$headerRow, $columns] = self::locateHeader($rows);

        if ($headerRow === null) {
            return ['header_row' => null, 'columns' => [], 'students' => []];
        }

        $students = [];

        foreach ($rows as $index => $row) {
            if ($index <= $headerRow) {
                continue;
            }

            $student = self::readRow($row, $columns);
            if ($student !== null) {
                // The sheet row number a human would see, for error messages.
                $student['row'] = $index + 1;
                $students[] = $student;
            }
        }

        return ['header_row' => $headerRow, 'columns' => $columns, 'students' => $students];
    }

    /**
     * The header is wherever it happens to be — first row in the template, twelfth in
     * the registrar's list under the letterhead. A row counts as the header once it
     * names a student number and some form of name, which no letterhead row does.
     *
     * @return array{0: int|null, 1: array<string, int>}
     */
    private static function locateHeader(array $rows): array
    {
        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $columns = [];

            foreach ($row as $column => $value) {
                $heading = self::normalize($value);
                if ($heading === '') {
                    continue;
                }

                foreach (self::HEADINGS as $field => $accepted) {
                    // First column wins: a sheet with both "name" and "first name"
                    // keeps whichever came first rather than overwriting.
                    if (!isset($columns[$field]) && in_array($heading, $accepted, true)) {
                        $columns[$field] = $column;
                        break;
                    }
                }
            }

            $hasName = isset($columns['name'])
                || (isset($columns['first_name']) && isset($columns['last_name']));

            if (isset($columns['student_number']) && $hasName) {
                return [$index, $columns];
            }

            // Stop before wandering into the data if the sheet has no header at all.
            if ($index > 40) {
                break;
            }
        }

        return [null, []];
    }

    /**
     * One row to one student, or null when the row is blank, a Female/Male divider or
     * the trailing "NOTHING FOLLOWS" marker.
     *
     * @return array<string, string>|null
     */
    private static function readRow(array $row, array $columns): ?array
    {
        $cell = fn (string $field) => isset($columns[$field])
            ? trim((string) ($row[$columns[$field]] ?? ''))
            : '';

        foreach ($row as $value) {
            $text = self::normalize($value);
            if ($text !== '' && in_array($text, self::NOT_A_STUDENT, true)) {
                return null;
            }
        }

        $studentNumber = $cell('student_number');
        $email = $cell('email');

        if (isset($columns['name'])) {
            [$firstName, $middleName, $lastName] = self::splitName($cell('name'));
        } else {
            $firstName = $cell('first_name');
            $middleName = $cell('middle_name');
            $lastName = $cell('last_name');
        }

        // A row with nothing identifying on it is padding, not a failed import.
        if ($studentNumber === '' && $firstName === '' && $lastName === '' && $email === '') {
            return null;
        }

        return [
            'student_number' => $studentNumber,
            'first_name'     => $firstName,
            'middle_name'    => $middleName,
            'last_name'      => $lastName,
            'email'          => self::cleanEmail($email),
            'phone_number'   => self::cleanPhone($cell('phone_number')),
        ];
    }

    /**
     * "AÑEZ, RONELYN R." -> [RONELYN, R., AÑEZ]. Everything before the first comma is
     * the surname, which keeps "ESPINOLA JR, JONATHAN V." intact; a single trailing
     * initial after it is the middle name. Casing is left exactly as the registrar
     * typed it rather than title-cased, which would turn names like MCDONALD into
     * Mcdonald.
     *
     * @return array{0: string, 1: string, 2: string} first, middle, last
     */
    public static function splitName(string $name): array
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name));
        if ($name === '') {
            return ['', '', ''];
        }

        if (str_contains($name, ',')) {
            [$last, $rest] = explode(',', $name, 2);
        } else {
            // No comma: assume "FIRST MIDDLE LAST" and take the final word as surname.
            $parts = explode(' ', $name);
            $last = count($parts) > 1 ? array_pop($parts) : $name;
            $rest = implode(' ', $parts);
        }

        $last = trim($last);
        $parts = array_values(array_filter(explode(' ', trim($rest)), fn ($p) => trim($p) !== '' && trim($p) !== '.'));

        $middle = '';
        if (count($parts) > 1) {
            $tail = end($parts);
            // A lone letter, with or without its full stop, is a middle initial.
            if (preg_match('/^\p{L}\.?$/u', $tail)) {
                $middle = array_pop($parts);
            }
        }

        return [implode(' ', $parts), $middle, $last];
    }

    /** Registrars write "N/A" where a student never gave an address. */
    private static function cleanEmail(string $value): string
    {
        $value = trim($value);

        return in_array(strtolower($value), ['n/a', 'na', 'none', '-'], true) ? '' : $value;
    }

    /**
     * Contact numbers come in typed by hand — "93582 6074 7" has stray spaces and
     * "09688t10176" has a stray letter. Keep the digits and a leading +, drop the rest.
     */
    private static function cleanPhone(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $plus = str_starts_with($value, '+') ? '+' : '';

        return $plus . preg_replace('/\D/', '', $value);
    }

    /** Lowercased, punctuation-free, single-spaced — so "STUD NO." matches "stud no". */
    private static function normalize(mixed $value): string
    {
        $text = mb_strtolower(trim((string) $value));
        $text = str_replace(['*'], '', $text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }
}

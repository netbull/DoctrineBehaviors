<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Sluggable;

use NetBull\DoctrineBehaviors\Exception\SluggableException;
use Symfony\Component\String\Slugger\AsciiSlugger;

trait SluggableMethodsTrait
{
    /**
     * @param string $slug
     * @return void
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Generates and sets the entity's slug. Called prePersist and preUpdate
     */
    public function generateSlug(): void
    {
        if ($this->slug !== null && $this->shouldRegenerateSlugOnUpdate() === false) {
            return;
        }

        $values = [];
        foreach ($this->getSluggableFields() as $sluggableField) {
            $values[] = $this->resolveFieldValue($sluggableField);
        }

        $this->slug = $this->generateSlugValue($values);
    }

    /**
     * @return bool
     */
    public function shouldGenerateUniqueSlugs(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    private function getSlugDelimiter(): string
    {
        return '-';
    }

    /**
     * @return bool
     */
    private function shouldRegenerateSlugOnUpdate(): bool
    {
        return true;
    }

    /**
     * @param array $values
     * @return string
     * @throws SluggableException
     */
    private function generateSlugValue(array $values): string
    {
        $usableValues = [];
        foreach ($values as $value) {
            if (! empty($value)) {
                $usableValues[] = $value;
            }
        }

        $this->ensureAtLeastOneUsableValue($values, $usableValues);

        // generate the slug itself
        $sluggableText = implode(' ', $usableValues);

        $unicodeString = (new AsciiSlugger())->slug($sluggableText, $this->getSlugDelimiter());

        return strtolower($unicodeString->toString());
    }

    /**
     * @param array $values
     * @param array $usableValues
     * @return void
     * @throws SluggableException
     */
    private function ensureAtLeastOneUsableValue(array $values, array $usableValues): void
    {
        if (count($usableValues) >= 1) {
            return;
        }

        throw new SluggableException(sprintf(
            'Sluggable expects to have at least one non-empty field from the following: ["%s"]',
            implode('", "', array_keys($values))
        ));
    }

    /**
     * @return mixed|null
     */
    private function resolveFieldValue(string $field): mixed
    {
        if (property_exists($this, $field)) {
            return $this->{$field};
        }

        $methodName = 'get' . ucfirst($field);
        if (method_exists($this, $methodName)) {
            return $this->{$methodName}();
        }

        return null;
    }
}

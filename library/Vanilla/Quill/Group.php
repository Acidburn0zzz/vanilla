<?php
/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace Vanilla\Quill;

use SebastianBergmann\CodeCoverage\Report\Text;
use Vanilla\Quill\Blots\AbstractBlot;
use Vanilla\Quill\Blots\AbstractLineBlot;
use Vanilla\Quill\Blots\AbstractListBlot;
use Vanilla\Quill\Blots\BlockquoteLineBlot;
use Vanilla\Quill\Blots\BulletedListBlot;
use Vanilla\Quill\Blots\CodeBlockBlot;
use Vanilla\Quill\Blots\Embeds\AbstractBlockEmbedBlot;
use Vanilla\Quill\Blots\Embeds\AbstractInlineEmbedBlot;
use Vanilla\Quill\Blots\HeadingBlot;
use Vanilla\Quill\Blots\OrderedListBlot;
use Vanilla\Quill\Blots\TextBlot;

/**
 * Class to represent a group of a quill blots. One block can contain multiple inline type blots.
 *
 * @package Vanilla\Quill
 */
class Group {

    /** @var AbstractBlot[] */
    private $blots = [];

    /**
     * @var AbstractBlot[]
     *
     * Blots that can determine the surrounding tag over the other blot types.
     */
    const OVERRIDING_BLOTS = [
        HeadingBlot::class,
        CodeBlockBlot::class,
        AbstractListBlot::class,
        AbstractLineBlot::class,
    ];

    /**
     * Create any empty block. When rendered it will output <p><br></p>
     *
     * @return Group
     */
    public static function makeEmptyGroup(): Group {
        $block = new Group();
        $blot = new TextBlot(["insert" => ""], [], []);
        $blot->setContent("<br>");
        $block->pushBlot($blot);
        return $block;
    }

    public function isBreakOnlyGroup(): bool {
        if (count($this->blots) !== 1) {
            return false;
        }

        $blot = $this->blots[0];
        return \get_class($blot) === TextBlot::class && $this->blots[0]->getContent() === "";
    }

    public function endsWithBlotOfType($blotClass, $needsExactMatch = false): bool {
        if (count($this->blots) === 0) {
            return false;
        }

        $lastBlot = $this->blots[count($this->blots) - 1];

        if ($needsExactMatch) {
            return \get_class($lastBlot) === $blotClass;
        } else {
            return $lastBlot instanceof $blotClass;
        }
    }

    /**
     * Render the block.
     *
     * @return string
     */
    public function render(): string {
        if (count($this->blots) === 0) {
            return "";
        }

        if (count($this->blots) === 1 && $this->blots[0] instanceof AbstractBlockEmbedBlot) {
            return $this->blots[0]->render();
        }

        $surroundTagBlot = $this->getBlotForSurroundingTags();
        $result = $surroundTagBlot->getGroupOpeningTag();

        if ($surroundTagBlot instanceof AbstractLineBlot) {
            $result .= $this->renderLineGroup($surroundTagBlot);
        } else {
            foreach ($this->blots as $blot) {
                $blotIsBreak = get_class($blot) === TextBlot::class && $blot->getContent() === "";
                if (!$this->isBreakOnlyGroup() && $blotIsBreak) {
                    continue;
                }

                $result .= $blot->render();
            }
        }

        $result .= $surroundTagBlot->getGroupClosingTag();
        return $result;
    }

    /**
     * @param AbstractLineBlot $lineBlot
     */
    private function renderLineGroup(AbstractLineBlot $lineBlot) {
        $result = "";

        $result .= $lineBlot->renderLineStart();

        foreach ($this->blots as $index => $blot) {
            // Ignore starting newlines.
            if ($index === 0 && $blot->getContent() === "") {
                continue;
            }
            $result .= $blot->render();

            if ($blot instanceof AbstractLineBlot) {
                $result .= $blot->renderLineEnd();
                $result .= $blot->renderNewLines();

                if ($index < count($this->blots) - 1) {
                    $result .= $lineBlot->renderLineStart();
                }
            }
        }

        return $result;
    }

    /**
     * Add a blot to this block.
     *
     * @param AbstractBlot $blot
     */
    public function pushBlot(AbstractBlot $blot) {
        $this->blots[] = $blot;
    }

    /**
     * Get the position in the blots array of the first blot of the given type. Defaults to -1.
     *
     * @param string $blotType
     *
     * @return int
     */
    public function getIndexForBlotOfType(string $blotType): int {
        $index = -1;

        foreach($this->blots as $blotIndex => $blot) {
            if($blot instanceof $blotType) {
                $index = $blotIndex;
                break;
            }
        }

        return $index;
    }

    private function getBlotForSurroundingTags(): AbstractBlot {
        $blot = $this->blots[0];

        foreach(static::OVERRIDING_BLOTS as $overridingBlot) {
            $index = $this->getIndexForBlotOfType($overridingBlot);
            if ($index >= 0) {
                $blot = $this->blots[$index];
                break;
            }
        }

        return $blot;
    }
}
